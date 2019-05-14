#!/bin/bash -x

# clear sqlite db and settings.php
sudo chown -R drupal:drupal /opt/drupal/web/sites/default
sudo chmod 755 /opt/drupal/web/sites/default
sudo rm -f /opt/drupal/web/sites/default/files/.ht*
sudo rm -f /opt/drupal/web/sites/default/settings.php
# re-install using mysql
cd /opt/drupal
drush site-install -y \
  --root=/opt/drupal \
  --db-url=mysql://${DBUSER}:${DBPASS}@${DBHOST}/${DBNAME} \
  --account-name=${ACCOUNT_NAME} \
  --account-pass=${ACCOUNT_PASS} \
  --account-mail=${ACCOUNT_MAIL} \
  --db-name="Neon Time"
# clone timesheet
mkdir -p /opt/drupal/web/modules/custom
cd /opt/drupal/web/modules/custom
git clone https://github.com/neontribe/timesheet.git
drush en -y timesheet
# clone theme
mkdir -p /opt/drupal/web/themes/custom
cd /opt/drupal/web/themes/custom
git clone https://github.com/neontribe/timesheet_iframe.git
drush then -y timesheet_iframe
# install bootstrap via composer
cd /opt/drupal
composer require drupal/bootstrap
# set bootstrap as default theme
cd /opt/drupal/web
drush then -y bootstrap
drush config-set system.theme default bootstrap
drush cr
sudo chown -R drupal:drupal /opt/drupal/web/sites/default

drush rs 0.0.0.0:${DR_PORT}
