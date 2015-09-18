# PHP DICOM Worklist Server

This is a standalone worklist SCP provider for a particular RIS called **Terra**.  The implementation
has been deliberately split into DMWL management classes and database interface classes so that it can be reused.

> See notes at the end about how to turn this into a worklist daemon for other schemas.

This is all very rudimentary.  Briefly, this application:

- Generates DCMTk "dump" files based on a data mapping class from a MySQL data source
- Converts those dump files into DCM .wl records using `dump2dcm`
- Provides a way to run `wlmscpfs` to use those files and pipe output to a log file


# License

## The MIT License (MIT)

Copyright (c) 2015 Wojtek Grabski

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
 
# Dependencies

- DCMTk binaries, either from Homebrew on Mac, or from the OFFIS website (installation instructions are provided below).


# Installation

Clone the project:

    $ git clone git@bitbucket.org:gamut/terra.dmwl.git
    $ cd terra.dmwl

If the first step fails, that's because you don't have a Bitbucket account, or haven't yet updated your SSH keys on the site.  Read [here](https://confluence.atlassian.com/bitbucket/how-to-install-a-public-key-on-your-bitbucket-account-276628835.html) to find out how to do that.

## Install DCMTk binaries

### Linux

On Debian (Ubuntu, etc):

    $ apt-get install dcmtk

On Fedora, CentOS, etc:

    $ yum install dcmtk
    
### Mac

First install Homebrew:

    $ ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
    $ brew doctor

Then install DCMTk:

    $ brew install dcmtk

### Both Linux and Mac

Then, create links to the relevant binaries (this is just in case paths aren't configured in your PHP
environment):

    $ ln -s $(which wlmscpfs) 3rd-party/dcmtk/bin/wlmscpfs
    $ ln -s $(which dump2dcm) 3rd-party/dcmtk/bin/dump2dcm

### Windows

Download DCMTk binaries:

    ftp://dicom.offis.de/pub/dicom/offis/software/dcmtk/dcmtk360/bin/dcmtk-3.6.0-win32-i386.zip

Windows does not support linking in the filesystem (without some extensions), so extract DCMTk directly into the
`terra.dmwl\3rd-party\dcmtk` folder.

> Make sure that `\3rd-party\dcmtk\bin` contains `wlmscpfs.exe` and `dump2dcm.exe`.

# Configuration

## Configuring the PHP application

Make a copy of global-headers/configuration.inc.template.php to global-headers/configuration.inc.php

Linux and Mac:

    $ cp global-headers/configuration.inc.template.php global-headers/configuration.inc.php  

Windows:

    > copy .\global-headers\configuration.inc.template.php .\global-headers\configuration.inc.php  


Then edit `global-headers/configuration.inc.php`.  Refer to comments throughout.  Be very sure to check and adjust, 
*DATABASE_*, and *DMWL_* parameters.

Also adjust *DCMTK_BIN_PATH* if you'd like to be more specific about the location 
of **dump2dcm** and **wlmscpfs**, but if you've followed the instructions above then you can leave that one as-is.

# Running the Maintenance Task

To create and prune worklist records, a periodic execution of the `scripts/update_orders.php` script is required.  The script:

- Deletes stale records that exceed **DMWL_MAX_AGE** age in days.  *Note: If you're not getting any records during your tests, this is probably the reason!*
- Checks if existing records have changed, updates and creates new .wl files in the worklist database folder.

## Windows 

To do this on Windows, merely create a scheduled task that runs PHP.EXE, and passes the full path and name of the script
as an argument, e.g.,

    C:\Program Files\PHP\bin\PHP.EXE C:\apps\terra_dmwl\scripts\update_orders.php

Set it to run every fifteen minutes.  Check the contents `logs/default.log` to ensure that it operates as expected.

> Hint: You can always drop to the command prompt and run this task manually to see if it spits out any errors.

## Linux and Mac

A cron task will suffice:

    $ crontab -e

Then add the following entry:

```
*/15 * * * * /usr/bin/php /home/username/terra_dwml/scripts/update_orders.php >/dev/null 2>&1
```

> Make sure to adjust the path to `update_orders.php` and `php` to reflect your environment!

Save and exit.


## The Worklist Daemon Script

To help with running the DMWL server, I've created a script that:

- Reads operating parameters from the DMWL_AE_TITLE, DMWL_PORT, and DMWL_DCM_PATH configuration options from `global-headers/configuration.inc.php`.  The
default port 1070 is because anything lower than 1024 requires elevation on Windows and root context on Linux and Mac.
- Executes wlmscpfs and redirects STDERR to `logs\wlmscpfs.stderr.log`

### Running the Daemon

#### Windows

To run the DICOM modality worklist daemon.

    > C:\Program Files\PHP\php c:\path_to\terra.dmwl\scripts\dmwl_deamon.php

You might try to use [NSSM](https://nssm.cc/) to turn this into a Windows service.

#### Posix

An upstart job will do. A sample configuration is provided in `docs/upstart/dmwl_daemon.conf`.   This script requires root privileges, and so it's best installed in  `/etc/init`:

    $ sudo cp docs/upstart/dmwl_daemon.conf /etc/init/
    $ sudo initctl reload-configuration

Then you can use the following command to start/stop the daemon:

    $ service dmwl_daemon start
    $ service dmwl_daemon stop
    $ service dmwl_daemon status
    
## Making it Work with other schemas

Everything is written to be modified.  To introduce another schema just clone the project and branch it.  Then:

Adjust the configuration:

- You'll probably only need to change the APP_SHORT and DATABASE parameters, but feel free to modify other things to suit your tastes.

Make a new data management class to pull records from your database:

1. Copy the TerraExamsMgmt.php class to a new file in the same folder, call it NewAppExamsMgmt, or something.
2. Modify the `QUERY_GET_EXAMS` constant to get all the required fields.
3. Adjust the `getBetweenDates` method, if needed.

Make a new data object and mapping class to turn that data into DICOM dump files:

1. Copy the TerraExam.php class in data-objects to a new file, call is something else, like NewAppExam.php.  It's
imperative that this class name end with `Exam` because of a simple check down the stack.
2. Make modifications to the class attribute list, if you think you need more fields.
3. Modify the `__construct` method so that it properly translates from your database row to the class attributes.  Make
sure you set **all** the attributes!
4. Modify the static `recent` method to appropriately call the `NewAppExamsMgmt` class created above.

Then make sure that **DMWL_SOURCE_CLASS** in `global-headers/configuration.inc.php` points to this new class!

And that's it.  Everything else should work properly.