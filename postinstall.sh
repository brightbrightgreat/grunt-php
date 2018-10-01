#!/bin/bash
#
# Post Install Script

cd lib/vendor/squizlabs/php_codesniffer/src/Standards

if [ ! -e Blobfolio ]; then
	echo -e "Adding Blobfolio standard."
	ln -s ../../../../../Blobfolio/ Blobfolio
fi

if [ ! -e WordPress ]; then
	echo -e "Adding WordPress standard."
	ln -s ../../../../wp-coding-standards/wpcs/WordPress WordPress
fi

if [ ! -e WordPress-Core ]; then
	echo -e "Adding WordPress-Core standard."
	ln -s ../../../../wp-coding-standards/wpcs/WordPress-Core WordPress-Core
fi

if [ ! -e WordPress-Docs ]; then
	echo -e "Adding WordPress-Docs standard."
	ln -s ../../../../wp-coding-standards/wpcs/WordPress-Docs WordPress-Docs
fi

if [ ! -e WordPress-Extra ]; then
	echo -e "Adding WordPress-Extra standard."
	ln -s ../../../../wp-coding-standards/wpcs/WordPress-Extra WordPress-Extra
fi

if [ ! -e WordPress-VIP ]; then
	echo -e "Adding WordPress-VIP standard."
	ln -s ../../../../wp-coding-standards/wpcs/WordPress-VIP WordPress-VIP
fi