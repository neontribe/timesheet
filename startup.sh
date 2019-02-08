#!/bin/bash

# /opt/drupal/vendor/bin/drush en -y neon_ldap
/opt/drupal/vendor/bin/drush en -y timesheet

drush rs 0.0.0.0:8888
