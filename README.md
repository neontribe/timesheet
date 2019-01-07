# Timesheet

Module to add time sheeting to drupal 8

## Testing with docker

    docker run -ti -p 8888:8888 --rm --name timeshite -v $(pwd):/opt/drupal/web/modules/custom/timesheet -e UID=$(id -u) -e GID=$(id -g) tobybatch/timeshite
    docker exec -ti timeshite drush -y en timesheet

### These commands will be handled by composer once we are package installing

    docker exec -ti timeshite composer --working-dir=/opt/drupal require drupal/duration_field

### These commands are native to our install

    docker exec -ti timeshite composer --working-dir=/opt/drupal require drupal/bootstrap
    docker exec -ti timeshite ../vendor/bin/drupal theme:install bootstrap
    docker exec -ti timeshite drush config-set system.theme default bootstrap

    docker exec -ti timeshite composer --working-dir=/opt/drupal require drupal/ldap
    docker exec -ti timeshite drush en -y ldap ldap_authentication ldap_user ldap_query ldap_servers


## importing from kimai

    select t.id u.username, a.name, t.start_time, t.duration, t.description, p.name, c.name from kimai2_timesheet t inner join kimai2_users u on t.user=u.id inner join kimai2_activities a on t.activity_id=a.id inner join kimai2_projects p on t.project_id=p.id inner join kimai2_customers c on p.customer_id=c.id;

    docker exec -i mysql mysql -B -u root -pchangeme kimai < kimai-dump.sql


    composer require drupal/migrate_source_csv
 
## Dev stuff

    docker run -ti -p 8888:8888 --rm --name timeshite \
        -v $(pwd)/modules:/opt/drupal/web/modules \
        -v $(pwd)/drupal.sqlite:/opt/drupal/web/sites/default/files/.ht.sqlite \
        -e UID=$(id -u) -e GID=$(id -g) \
        tobybatch/timeshite

