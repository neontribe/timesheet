#!/bin/bash

sed "s/^M/, /g" export.orig.csv > export.csv
sed -i "s/alex/Alex Moore/g" export.csv
sed -i "s/Andy Barnes/Andy Barnes/g" export.csv
sed -i "s/Charlie/Charles Strange/g" export.csv
sed -i "s/George/George Deeks/g" export.csv
sed -i "s/harry/Harry Harrold/g" export.csv
sed -i "s/Holly/Holly Stringer/g" export.csv
sed -i "s/Jermalkl/Karl Jermy/g" export.csv
sed -i "s/katjam/Katja Mordaunt/g" export.csv
sed -i "s/neil/Neil Dabson/g" export.csv
sed -i "s/neontribe/superadmin/g" export.csv
sed -i "s/nick/Nick Wade/g" export.csv
sed -i "s/RobRogers/Rob Preus-MacLaren/g" export.csv
sed -i "s/rose.neontribe/Rose Bonner/g" export.csv
sed -i "s/steph/Steph Adams/g" export.csv
sed -i "s/tobias/Toby Batch/g" export.csv
