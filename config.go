package main

import (
	"encoding/json"
	"os"
	"strings"
)

type Placeholder struct {
	value string
}

func (p Placeholder) Resolve(variables map[string]string) string {
	resolved := p.value

	for key, value := range variables {
		resolved = strings.ReplaceAll(resolved, ":"+key, value)
	}

	return resolved
}

func (p *Placeholder) UnmarshalJSON(bytes []byte) error {
	var value string
	err := json.Unmarshal(bytes, &value)
	if err != nil {
		return err
	}

	p.value = value
	return nil
}

type ReleaseNotesOptions struct {
	Title        Placeholder `json:"title"`
	CommitFormat Placeholder `json:"commit_format"`
	Ignore       string      `json:"ignore"`
}

type Configuration struct {
	Remote        string              `json:"remote"`
	ReleaseNotes  ReleaseNotesOptions `json:"release_notes"`
	TagMessage    Placeholder         `json:"tag_message"`
	BeforeRelease []string            `json:"before_release"`
	AfterRelease  []string            `json:"after_release"`
	Assets        []string            `json:"assets"`
}

var Config *Configuration

func init() {
	_, err := os.Stat("rt.json")

	defaultConfig := Configuration{}

	if os.IsNotExist(err) {
		Config = &defaultConfig
		return
	}

	b, err := os.ReadFile("rt.json")
	check(err)

	config := defaultConfig

	err = json.Unmarshal(b, &config)
	check(err)

	if config.Remote == "" {
		config.Remote = "origin"
	}

	if config.ReleaseNotes.Title.value == "" {
		config.ReleaseNotes.Title.value = "Release :tag"
	}

	if config.ReleaseNotes.CommitFormat.value == "" {
		config.ReleaseNotes.CommitFormat.value = "* :hash: :message"
	}

	if config.TagMessage.value == "" {
		config.TagMessage.value = "Version :tag"
	}

	Config = &config
}
