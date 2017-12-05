# Please do not manually call this file!
# This script is run by the docker container when it is "run"

# Run the apache process in the background
/usr/sbin/apache2 -D APACHE_PROCESS &

# Immediately kickoff the task_executor script so we dont have to wait for the cron to activate
/usr/bin/php /var/www/downloader/project/scripts/task_executor/main.php

# Start the cron service in the foreground
# We dont run apache in the FG, so that we can restart apache without container
# exiting.
cron -f
