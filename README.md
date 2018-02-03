### Task

Create directory-explorer which shows hierarchy of directories and files inland beginning with a folder containing it.

### Requirements

* Have to work both in server and in cli mode.

* In case of symbolic links the recursion have not to be infinite.

* In case of additional parameters (transmitted via terminal in cli mode and via URL in server mode) have to highlight the transmitted words in every file and folder in hierarchy which contain.

* In case of impossibility opening file (no access rights) have to show a warning, not crush with exceptions.

### Readiness

Done.

### Usage

* To run the directory-explorer

```bash
php index.php bin
```

where

* `bin` is a text which you wont to highlight.
