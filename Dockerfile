FROM tobybatch/drupal

# RUN composer --working-dir=/opt/drupal update
RUN composer require --working-dir=/opt/drupal drush/drush
RUN composer require --working-dir=/opt/drupal drupal/bootstrap
RUN composer require --working-dir=/opt/drupal drupal/duration_field
RUN composer require --working-dir=/opt/drupal drupal/ldap
RUN composer require --working-dir=/opt/drupal drupal/views_data_export
RUN composer require --working-dir=/opt/drupal drupal/computed_field
RUN composer require --working-dir=/opt/drupal drupal/restui

ADD . modules/custom/timesheet
ADD .docker/startup.sh /startup.sh

ENTRYPOINT /startup.sh
