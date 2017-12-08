#!/bin/bash

read login
read password

url=$1
permission=$2

if [ -n "${permission}" ]; then
	url="${url}?permission=${permission}"
fi

curl -s -k -i -X GET -u "${login}:${password}" "${url}" | grep -q '200 OK'
exit $?
