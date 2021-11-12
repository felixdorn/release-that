package main

import (
	"fmt"
	"golang.org/x/term"
	"os"
	"syscall"
)

type Color [3]uint8

var PrintAnsi bool

var Gray Color = [3]uint8{147, 148, 153}
var Red Color = [3]uint8{239, 68, 68}
var White Color = [3]uint8{255, 255, 255}
var Yellow Color = [3]uint8{245, 158, 11}
var Green Color = [3]uint8{16, 185, 129}
var Stop string
var Bold string

func check(err error) {
	if err != nil {
		fmt.Fprintln(os.Stderr, Red.Fg()+"Error: "+err.Error()+Stop)
		os.Exit(1)
	}
}

func (c Color) Fg() string {
	if !PrintAnsi {
		return ""
	}

	return fmt.Sprintf("%s\033[38;2;%d;%d;%dm", Bold, c[0], c[1], c[2])
}

func init() {
	for _, arg := range os.Args {
		if arg == "--no-ansi" {
			PrintAnsi = false
			return
		}
	}

	PrintAnsi = true
	Stop = "\033[0m"
	Bold = "\033[1m"
}

func password(label string) []byte {
	fmt.Printf("%s%s\n=%s", White.Fg(), label, Stop)
	password, err := term.ReadPassword(syscall.Stdin)
	check(err)

	return password
}
