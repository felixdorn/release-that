package main

import (
	"context"
	"fmt"
	"os"

	"github.com/google/go-github/v40/github"
	"github.com/mitchellh/go-homedir"
	"golang.org/x/oauth2"
)

var GithubClient *github.Client

func init() {
	home, err := homedir.Dir()
	check(err)

	_, err = os.Stat(home + "/.rtauth")

	runAsGuest := false

	for _, arg := range os.Args {
		if arg == "--login" || arg == "--help" || arg == "--self-update" || arg == "--init" || arg == "--version" {
			runAsGuest = true
		}
	}

	if !runAsGuest && os.IsNotExist(err) {
		fmt.Printf("%sYou are not connected to GitHub.%s\n", Yellow.Fg(), Stop)
		fmt.Printf("%sPlease create a token at https://github.com/settings/tokens/new?scopes=repo.%s\n", Yellow.Fg(), Stop)
		fmt.Printf("%sThen run `rt --login` and paste your token when asked.%s\n", Yellow.Fg(), Stop)
		os.Exit(1)
	}

	if !runAsGuest {
		bytes, err := os.ReadFile(home + "/.rtauth")

		check(err)
		ctx := context.Background()
		ts := oauth2.StaticTokenSource(&oauth2.Token{
			AccessToken: string(bytes),
		})
		tc := oauth2.NewClient(ctx, ts)
		GithubClient = github.NewClient(tc)
	} else {
		GithubClient = github.NewClient(nil)
	}

}
