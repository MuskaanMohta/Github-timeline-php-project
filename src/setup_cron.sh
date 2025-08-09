#!/bin/bash
# This script should set up a CRON job to run cron.php every 5 minutes.
# You need to implement the CRON setup logic here.

#Minute(0-59) hour(0-23) day_of_month(1-31) month(1-12) day_of week(1-7) (command or scrip that will run) here every 5 min hence */5 * * * * and script to run at every 5 sec is cron.php its whole path is given where $(pwd)--gives full path to current directory
cron_job="*/5 * * * * php $(pwd)/src/cron.php" #schedule job for specific time

#crontab -l ->view current logged in users entries 
#2> means to redirect to stderr(standard error output) 2>/dev/null => suppress erors if no contrab users
#grep->search lines of text -F=>treat the pattern($cron_job) as fixed string -v=>select lines that do not match $cron_job  pattern here is $cron_job
#grep -Fv "$cron_job" filter out jobs if already present
#append the new job using echo
#entire o/p is pipped into crontab -
(crontab -l 2>/dev/null | grep -Fv "$cron_job"; echo "$cron_job")|crontab -

echo "Cron Job set to run cron.php every 5 minutes"  #print message