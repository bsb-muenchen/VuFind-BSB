#!/bin/bash
# Cleanup VuFind databases
#
# Copyright (C) Bayerische Staatsbibliothek 2024.
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License version 2,
# as published by the Free Software Foundation.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
#
# http://opensource.org/licenses/gpl-2.0.php GNU General Public License

# Should be called by cron, e.g. by a symlink in /etc/cron.daily/

# Before VuFind 10.0 expire_searches wants to write into the logfile, therefor we have to give write permissions:
# chmod 777 /var/log/vufind/ && chmod 666 /var/log/vufind/*
# After upgrading to VuFind 10.0 we can remove these permissions again:
# chmod 755 /var/log/vufind/ && chmod 644 /var/log/vufind/*

# path of the VuFind installation
# On development workstations a symlink can be set to the actual path.
VUFIND_HOME=/usr/local/vufind

# directory in VUFIND_HOME containing the directories of the views
viewsdir=bsb

# detect if not much disk space is left
reclaim=$( df /var/lib/mysql | grep '[0987][0-9]%' )

for viewdir in $VUFIND_HOME/$viewsdir/*
do
    # recognize directories of views
    if [ -d $viewdir/cache -a -f $viewdir/config/vufind/config.ini ]
    then
        echo $viewdir

        # expire searches
        export VUFIND_LOCAL_DIR=$viewdir
        php $VUFIND_HOME/public/index.php util/expire_searches 7

        # reclaim disk space
        if [ "$reclaim" ]
        then
                database=$( grep ^database $viewdir/config/vufind/config.ini | sed s/\"//g )
                dbname=$( echo $database | cut -d / -f 4 )
                dbauth=$( echo $database | cut -d / -f 3 | cut -d @ -f 1 )
                dbuser=$( echo $dbauth | cut -d : -f 1 )
                dbpass=$( echo $dbauth | cut -d : -f 2 )
                echo "OPTIMIZE TABLE search;" | mysql -u$dbuser -p$dbpass $dbname
        fi

    fi
done
