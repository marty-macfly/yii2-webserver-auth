#!/bin/bash

read login
read password

url=$1
permission=$2
token=''

if [ -n "$permission" ]; then
	url="${url}?permission=${permission}"
fi

if [ "$login" = "x-oauth-basic" ]; then
	token=$password
elif [ "$password" = "x-oauth-basic" ]; then
	token=$login
fi

if [ -n "$token" ]; then
	curl -s -k -i -X GET --header "x-sso-token: ${token}" "${url}" | grep -q '200 OK'
	exit $?
fi

exit 1
