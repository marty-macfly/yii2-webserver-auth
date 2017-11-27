#!/bin/bash

read login
read password

url=$1
permission=$2
token_name='x-sso-token'
token=''

if [ -n "${permission}" ]; then
	url="${url}?permission=${permission}"
fi

if [ "${login}" = "${token_name}" ]; then
	token=${password}
elif [ "${password}" = "${token_name}" ]; then
	token=${login}
fi

if [ -n "${token}" ]; then
	curl -s -k -i -X GET --header "${token_name}: ${token}" "${url}" | grep -q '200 OK'
	exit $?
fi

exit 1
