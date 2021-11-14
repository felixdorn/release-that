# Rt

Rt, short for release that, is a tool for quickly creating GitHub releases.

## Installation

```bash
curl -L https://github.com/felixdorn/rt/releases/latest/download/rt -o rt
chmod +x ./rt
mv ./rt /usr/bin/rt
```

Once you have installed the binary, you'll need to login to GitHub.

```bash
rt --login
```

Your personal access token is stored in `~/.rtauth` with restricted permissions (0600).

## Configuration

`rt` can work without any configuration, however if you want to customize the release name, contents..., you can create
one with the following command:

```bash
rt --init
```

The default configuration can be found [here](_config.json), it looks like this:

```json
{
  "release_notes": {
    "title": "Release :version",
    "commit_format": "* :hash: :message"
  },
  "before_release": [],
  "after_release": [],
  "assets": [],
  "tag_message": "Version :tag"
}
```

<!-- This sentence has been written by Github Copilot ¯\_(ツ)_/¯ -->
`before_release` and `after_release` are an array of commands that will be executed before and after the release
process.

`assets` is an array of files that will be uploaded along with the release.

You can use various placeholders in the following keys:

* tag_message:
    * `:tag` / `:version`
* release_notes.title:
    * `:tag` / `:version`
* release_notes.commit_format:
    * `:hash`
    * `:longHash`
    * `:message`
    * `:author.name`
    * `:author.email`

## Usage

You can release a new version with the following command:

```bash
rt --patch
rt --minor
rt --major
rt --custom 4.24.5-linux+stripped 
```

The custom version must be a valid semver version.