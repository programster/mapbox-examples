#!/bin/bash

# ensure running bash
if ! [ -n "$BASH_VERSION" ];then
    echo "this is not bash, calling self with bash....";
    SCRIPT=$(readlink -f "$0")
    /bin/bash $SCRIPT
    exit;
fi

# Setup for relative paths.
SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT") 
cd $SCRIPTPATH

# load the variables from the relative path
source ../../settings/docker_settings.sh

CONTAINER_IMAGE="`echo $REGISTRY`/`echo $PROJECT_NAME`"

# Kill the downloader if it is already running.
docker kill $PROJECT_NAME
docker rm $PROJECT_NAME


#######################
# ensure that we are running the frontend proxy 
# which allows us to run multiple web containers
RESULT=`docker ps | grep jwilder | wc -l`

if [ $RESULT -gt 0 ];then
    echo "found frontend proxy."
else
    echo "Deploying frontend proxy"
    docker run -d \
    -p 80:80 \
    --restart=always \
    -v /var/run/docker.sock:/tmp/docker.sock \
    -t jwilder/nginx-proxy
fi
#######################


# Now start the downloader container.
docker run -d \
-e VIRTUAL_HOST=$VIRTUAL_HOST \
--restart=always \
-p 80 \
-p 443 \
--name="$PROJECT_NAME" \
$CONTAINER_IMAGE

