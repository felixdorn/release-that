package main

import (
	"bytes"
	"context"
	_ "embed"
	"fmt"
	"io/ioutil"
	"net/url"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
	"time"

	"github.com/go-git/go-git/v5"
	"github.com/go-git/go-git/v5/plumbing"
	"github.com/google/go-github/v40/github"
	"github.com/me/rt/globals"
	"github.com/mitchellh/go-homedir"
	"github.com/spf13/cobra"
)

//go:embed _config.json
var defaultConfig []byte
var Start time.Time

var initialize bool
var login bool

var overwriteConfig bool

var noAnsi bool

var patch bool
var minor bool
var major bool
var customVersion string
var dryRun bool
var skipHooks string
var quiet bool
var selfUpdate bool
var version bool

var Repository *git.Repository

func main() {
	cli := cobra.Command{
		Use:   "rt",
		Short: "Automated release system for GitHub",
		RunE: func(cmd *cobra.Command, args []string) error {
			if version {
				fmt.Println("Rt", globals.Version)
				return nil
			}

			if initialize {
				_, err := os.Stat("rt.json")

				if err == nil && !overwriteConfig {
					return fmt.Errorf("a config file already exists! You can overwrite it with `--force`")
				}

				err = os.WriteFile("rt.json", defaultConfig, 0777)
				check(err)

				fmt.Printf("%sSuccessfully created a new %s`rt.json`%s config file. %s\n", Green.Fg(), White.Fg(), Stop+Green.Fg(), Stop)

				return nil
			}

			if login {
				fmt.Printf("%sThe token you're about to enter will be stored in %s~/.rtauth%s.%s\n", Gray.Fg(), White.Fg(), Gray.Fg(), Stop)
				fmt.Printf("%sYour can paste your token safely as it will be hidden immediately.%s\n\n", Gray.Fg(), Stop)
				token := password("Personal access token")

				home, err := homedir.Dir()
				check(err)

				err = os.WriteFile(home+"/.rtauth", token, 0600)
				check(err)

				fmt.Printf("\n\n%sYour token has been saved.%s\n", Green.Fg(), Stop)

				return nil
			}

			if selfUpdate {
				// get the latest release from GitHub
				release, _, err := GithubClient.Repositories.GetLatestRelease(context.Background(), "felixdorn", "release-that")
				check(err)

				if len(release.Assets) == 0 {
					return fmt.Errorf("no assets found for this release, can't update")
				}

				asset, _, err := GithubClient.Repositories.DownloadReleaseAsset(context.Background(), "felixdorn", "release-that", *release.Assets[0].ID, GithubClient.Client())
				check(err)

				body, err := ioutil.ReadAll(asset)
				check(err)

				executable, err := os.Executable()
				check(err)

				err = os.WriteFile(executable+".tmp", body, 0777)
				check(err)

				// rename the rt.tmp to rt
				err = os.Rename(executable+".tmp", executable)
				check(err)

				// chmod rt to 0777
				err = os.Chmod(executable, 0777)
				check(err)

				fmt.Printf("%sSuccessfully updated to version %s%s.%s\n", Green.Fg(), White.Fg(), *release.TagName, Stop)
				return nil
			}

			if !patch && !minor && !major && customVersion == "" {
				return fmt.Errorf("one of the following flags is required --patch, --minor, --major, --custom")
			}

			return execute()
		},
	}

	cli.Flags().BoolVarP(&initialize, "init", "i", false, "Create a new configuration file")
	cli.Flags().BoolVarP(&overwriteConfig, "force", "f", false, "Overwrite an existing config file")
	cli.Flags().BoolVar(&patch, "patch", false, "Increment by a patch version")
	cli.Flags().BoolVar(&minor, "minor", false, "Increment by a minor version")
	cli.Flags().BoolVar(&major, "major", false, "Increment by a major version")
	cli.Flags().StringVar(&customVersion, "custom", "", "Set a new custom version")
	cli.Flags().BoolVar(&login, "login", false, "Login to GitHub")
	cli.Flags().BoolVar(&noAnsi, "no-ansi", false, "Disable ANSI colors")
	cli.Flags().BoolVar(&dryRun, "dry-run", false, "Run without making any changes")
	cli.Flags().StringVar(&skipHooks, "skip-hooks", "no", "Skip one or many hooks separated by a comma")
	cli.Flags().BoolVarP(&quiet, "quiet", "q", false, "Reduced output")
	cli.Flags().BoolVarP(&selfUpdate, "self-update", "u", false, "Update rt to the latest version")
	cli.Flags().BoolVarP(&version, "version", "v", false, "Print the version")
	cli.SilenceErrors = true
	cli.SilenceUsage = true
	err := cli.Execute()
	check(err)
}

func init() {
	Start = time.Now()

	_, err := os.Stat(".git")

	if os.IsNotExist(err) {
		check(
			fmt.Errorf("the current directory is not a git repository"),
		)
	}

	wd, _ := os.Getwd()

	repository, err := git.PlainOpen(wd)
	check(err)

	Repository = repository

	_, err = Repository.Head()
	if err != nil {
		fmt.Printf("%sCan not get the repository HEAD. Do you have any commits?%s\n", Yellow.Fg(), Stop)
		check(err)
	}
}

func execute() error {
	remote, err := Repository.Remote(Config.Remote)
	check(err)

	current, nextVersion := getNextVersion()
	releaseNotes := buildReleaseNotes(current)
	owner, project := findOwnerAndProjectName(remote.Config().URLs[0])

	if !shouldSkipHook("before_release") {
		for _, hook := range Config.BeforeRelease {
			cmd := exec.Command("sh", "-c", Placeholder{value: hook}.Resolve(map[string]string{
				"version": nextVersion,
				"tag":     nextVersion,
			}))
			cmd.Stdin = os.Stdin
			cmd.Stdout = os.Stdout
			cmd.Stderr = os.Stderr
			err := cmd.Run()
			check(err)
		}
	}

	name := Config.ReleaseNotes.Title.Resolve(map[string]string{
		"version": nextVersion,
		"tag":     nextVersion,
	})

	if !dryRun {
		release, _, err := GithubClient.Repositories.CreateRelease(context.Background(), owner, project, &github.RepositoryRelease{
			Name:    &name,
			TagName: &nextVersion,
			Body:    &releaseNotes,
		})
		check(err)

		for _, asset := range Config.Assets {
			file, err := os.Open(asset)
			check(err)

			_, _, err = GithubClient.Repositories.UploadReleaseAsset(context.Background(), owner, project, *release.ID, &github.UploadOptions{
				Name: filepath.Base(asset),
			}, file)
			check(err)
		}
	}

	if !shouldSkipHook("after_release") {
		for _, hook := range Config.AfterRelease {
			cmd := exec.Command("sh", "-c", Placeholder{value: hook}.Resolve(map[string]string{
				"version": nextVersion,
				"tag":     nextVersion,
			}))
			cmd.Stdin = os.Stdin
			cmd.Stdout = os.Stdout
			cmd.Stderr = os.Stderr
			err := cmd.Run()
			check(err)
		}
	}

	if !dryRun {
		Repository.Fetch(nil)
	}

	if !quiet {
		fmt.Printf("Release %s\n\n", nextVersion)
		fmt.Print(releaseNotes)
		fmt.Printf("\nReleased in %.2fs\n", (float64(time.Now().UnixNano())-float64(Start.UnixNano()))/1000_000_000.0)
	} else {
		fmt.Print(nextVersion)
	}

	return nil
}

func getNextVersion() (*plumbing.Reference, string) {
	tag := getLatestTag()

	var current *Version

	if tag == nil {
		current = MustParse("0.0.0")
	} else {
		nv, err := NewVersion(tag.Name().Short())
		check(err)
		current = nv
	}

	if patch {
		return tag, current.IncPatch().String()
	} else if minor {
		return tag, current.IncMinor().String()
	} else if major {
		return tag, current.IncMajor().String()
	}

	next, err := NewVersion(customVersion)
	check(err)
	return tag, next.String()
}

func getLatestTag() *plumbing.Reference {
	tags, err := Repository.Tags()
	check(err)

	var tag *plumbing.Reference

	for {
		nextTag, _ := tags.Next()

		if nextTag == nil {
			break
		}

		tag = nextTag
	}

	return tag
}

func findOwnerAndProjectName(remoteUrl string) (string, string) {
	if strings.HasPrefix(remoteUrl, "git") {
		op := strings.Split(
			remoteUrl[15:],
			"/",
		)

		return op[0], strings.TrimSuffix(op[1], ".git")
	}

	u, err := url.Parse(remoteUrl)
	check(err)

	path := strings.Split(u.Path, "/")

	return path[0], strings.TrimSuffix(path[1], path[1])
}

func buildReleaseNotes(latestTag *plumbing.Reference) string {
	if latestTag == nil {
		return "Initial release.\n"
	}

	var buffer bytes.Buffer

	cmd := exec.Command("git", "log", "--pretty=%H", latestTag.Name().Short()+"..HEAD")
	out, err := cmd.CombinedOutput()
	check(err)
	for _, c := range strings.Split(string(out), "\n") {
		if c == "" {
			continue
		}

		// get the commit message
		commit, err := Repository.CommitObject(plumbing.NewHash(c))
		check(err)

		messageLines := strings.Split(commit.Message, "\n")

		var message string

		if len(messageLines) == 0 {
			continue
		}

		message = messageLines[0]

		if len(messageLines) > 2 {
			message += "..."
		}

		_, _ = fmt.Fprintf(&buffer, strings.TrimSpace(Config.ReleaseNotes.CommitFormat.Resolve(map[string]string{
			"hash":         c[0:8],
			"longHash":     c,
			"message":      message,
			"author.name":  commit.Author.Name,
			"author.email": commit.Author.Email,
		}))+"\n")
	}

	return buffer.String()
}

func shouldSkipHook(hook string) bool {
	if skipHooks == "no" {
		return false
	}

	if skipHooks == "" {
		return true
	}

	return strings.Contains(skipHooks, hook)
}
