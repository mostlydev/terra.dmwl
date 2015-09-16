# Terra DICOM Worklist Server

This is a standalone worklist provider for the `terradb` application.  It makes use of the
following:

  - DCMTk binaries, either from Homebrew on Mac, or from the OFFIS website

It is a very rudimentary implementaiton.  Briefly, it:

- Prunes old worklist records from a wlmscpfs data directory
- Queries the terra database
- Generates dcmtk DCM dump files using the available data in the exam table
- Converts those dump files into DCM using dump2dcm
- Provides a way to run wlmscpfs and pipe output to a log file



## Installing DCMTk binaries

### Linux and Mac

First install DCMTk

On Linux:

    $ apt-get install dcmtk
    
On Mac:

    $ ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
    $ brew doctor
    $ brew install dcmtk

Then, create links to the relevant binaries (this is just in case paths aren't configured in your PHP
environment):

    $ cd 3rd-party/dcmtk
    $ ln -s $(which wlmscpfs) wlmscpfs
    $ ln -s $(which dump2dcm) dump2dcm

### Windows

Download DCMTk binaries:

    ftp://dicom.offis.de/pub/dicom/offis/software/dcmtk/dcmtk360/bin/dcmtk-3.6.0-win32-i386.zip

Windows does not support linking in the filesystem (without some extensions), so extract DCMTk directly into the
`\application\3rd-party\dcmtk` folder.

Make sure that `\3rd-party\dcmtk\bin` contains `wlmscpfs.exe` and `dump2dcm.exe`.

## Configuring the application

### Linux and Mac

    $ chmod +x tools/init-config
    $ tools/init-config

### Windows

Make a copy of global-headers/configuration.inc.template.php to global-headers/configuration.inc.php

    \:> copy .\global-headers\configuration.inc.template.php .\global-headers\configuration.inc.php  
    \:> copy .\scripts\includes\require-globals.template.php .\scripts\includes\require-globals.php  


Edit `global-headers/configuration.inc.php`.  Refer to comments throughout.
Edit `scripts\includes\require-globals.php` to refer to the proper path.

