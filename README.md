# Terra DICOM Worklist Server

This is a standalone worklist SCP provider for the `terradb` application.  The implementation is deliberately split into 
DMWL management classes and database interface classes.  See notes at the end about how to make this a worklist daemon
for other database schemas.

This is all very rudimentary.  Briefly, this application:

- Generates DCMTk "dump" files based on a data mapping class
- Converts those dump files into DCM .wl records using dump2dcm
- Provides a way to run wlmscpfs and pipe output to a log file


# Dependencies

- DCMTk binaries, either from Homebrew on Mac, or from the OFFIS website (installation instructions are provided below).


# Installation

Clone the project:

    $ git clone git@bitbucket.org:gamut/terra.dmwl.git
    $ cd terra.dmwl

If the first step fails, that's because you don't have a Bitbucket account, or haven't yet updated your SSH keys on the site.  Read [here](https://confluence.atlassian.com/bitbucket/how-to-install-a-public-key-on-your-bitbucket-account-276628835.html) to find out how.

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

    $ cd 3rd-party/dcmtk/bin
    $ ln -s $(which wlmscpfs) wlmscpfs
    $ ln -s $(which dump2dcm) dump2dcm

### Windows

Download DCMTk binaries:

    ftp://dicom.offis.de/pub/dicom/offis/software/dcmtk/dcmtk360/bin/dcmtk-3.6.0-win32-i386.zip

Windows does not support linking in the filesystem (without some extensions), so extract DCMTk directly into the
`terra.dmwl\3rd-party\dcmtk` folder.

> Make sure that `\3rd-party\dcmtk\bin` contains `wlmscpfs.exe` and `dump2dcm.exe`.

# Configuration

## Configuring the PHP application

### Linux and Mac

    $ chmod +x tools/init-config
    $ tools/init-config

### Windows

Make a copy of global-headers/configuration.inc.template.php to global-headers/configuration.inc.php

    \:> copy .\global-headers\configuration.inc.template.php .\global-headers\configuration.inc.php  


Then edit `global-headers/configuration.inc.php`.  Refer to comments throughout.  Be very sure to check and adjust *APP_ROOT*, *DATABASE_*, and *DMWL_* parameters. Also adjust *DCMTK_BIN_PATH* if you'd like to be more specific about the location of **dump2dcm** and **wlmscpfs**, but if you've followed the instructions above then you can leave them as-is.

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

> Make sure to adjust the path to `update_orders.php` to reflect your environment!

Save and exit.


## The Worklist Daemon Script

To help with running the DMWL server, I've created a script that:

- Reads operating parameters from the DMWL_AE_TITLE, DMWL_PORT, and DMWL_DCM_PATH configuration options from `global-headers/configuration.inc.php`.  The
default port 1070 is because anything lower than 1024 requires elevation on Windows and root context on Linux and Mac.
- Executes wlmscpfs and redirects STDERR to `logs\wlmscpfs.stderr.log`

### Running the Daemon

#### Windows

To run the DICOM modality worklist daemon.

    :> C:\Program Files\PHP\php c:\path_to\terra.dmwl\scripts\dmwl_deamon.php

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

- You'll probably only need to change the DATABASE_NAME parameter, but feel free to modify other things that include
the application name.

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

And that's it.  Everything else should work properly.

