package main

import (
	"bytes"
	"fmt"
	"regexp"
	"strconv"
)

type Version struct {
	Major      int
	Minor      int
	Patch      int
	Prerelease string
	Metadata   string
}

func NewVersion(version string) (*Version, error) {
	compiler := regexp.MustCompile("^(0|[1-9]\\d*)\\.(0|[1-9]\\d*)\\.(0|[1-9]\\d*)(?:-((?:0|[1-9]\\d*|\\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\\.(?:0|[1-9]\\d*|\\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\\+([0-9a-zA-Z-]+(?:\\.[0-9a-zA-Z-]+)*))?$")

	if !compiler.MatchString(version) {
		return nil, fmt.Errorf("invalid version: %s", version)
	}

	details := compiler.FindStringSubmatch(version)

	// convert the matched strings to integers
	major, _ := strconv.Atoi(details[1])
	minor, _ := strconv.Atoi(details[2])
	patch, _ := strconv.Atoi(details[3])

	// return a new version
	v := Version{
		Major:      major,
		Minor:      minor,
		Patch:      patch,
		Prerelease: details[4],
		Metadata:   details[5],
	}

	return &v, nil
}

func (v *Version) IncMajor() *Version {
	v.Major++
	v.Minor = 0
	v.Patch = 0
	v.Prerelease = ""
	v.Metadata = ""

	return v
}
func (v *Version) IncMinor() *Version {
	v.Minor++
	v.Patch = 0
	v.Prerelease = ""
	v.Metadata = ""
	return v
}

func (v *Version) IncPatch() *Version {
	v.Patch++
	v.Prerelease = ""
	v.Metadata = ""

	return v
}

func (v *Version) String() string {
	var buffer bytes.Buffer

	// write the version
	buffer.WriteString(fmt.Sprintf("%d.%d.%d", v.Major, v.Minor, v.Patch))

	// write the prerelease if not empty
	if v.Prerelease != "" {
		buffer.WriteString("-" + v.Prerelease)
	}

	// write the metadata if not empty
	if v.Metadata != "" {
		buffer.WriteString("+" + v.Metadata)
	}

	return buffer.String()
}

func MustParse(version string) *Version {
	v, err := NewVersion(version)
	if err != nil {
		panic(err)
	}

	return v
}
