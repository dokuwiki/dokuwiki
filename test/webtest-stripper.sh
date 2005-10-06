#!/bin/sh
################################################################################
# Quick script to make simpletest web test fail output more legible
#
# Run the web test group from the command line w/ the command:
#
# $ ./runtests.php -g [GROUP] 2> tmp
#
# redirecting the error messages to the file tmp
#
# Then run this command on the tmp file
#
# $ ./webtest-stripper.sh tmp
#
################################################################################

usage="Usage: ${0} [WEB_TEST_OUTPUT_FILE]";

if [ -z "$1" ]; then
    echo $usage;
    exit 1;
elif [ ! -f "$1" ]; then
    echo "${1} is not a file!";
    echo $usage;
    exit 1;
fi

sed -e 's/\\n/\
/g' "${1}" |\
sed -e 's/\\//g' |\
sed -e 's/FAIL.*Pattern \[\#\^/EXPECTED:\
/g' |\
sed -e 's/\$#i\].*string \[/\
\
GOT:\
/g' |\
sed -e 's/\]$/\
----------------------------------------------------------------\
/g'

exit 0