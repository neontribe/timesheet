# Timesheet

Module to add time sheeting to drupal 8

## Testing with docker

    docker-compose up

### These commands are native to our install

    docker exec -ti timeshite composer --working-dir=/opt/drupal require drupal/bootstrap
    docker exec -ti timeshite ../vendor/bin/drupal theme:install bootstrap
    docker exec -ti timeshite drush config-set system.theme default bootstrap

    docker exec -ti timeshite composer --working-dir=/opt/drupal require drupal/ldap
    docker exec -ti timeshite drush en -y ldap ldap_authentication ldap_user ldap_query ldap_servers

## importing from kimai

    select \
        t.id          as id,       \
        c.name        as customer, \
        p.name        as project,  \
        a.name        as activity, \
        t.start_time  as ddate,    \
        t.duration    as duration, \
        t.description as title,    \
        u.username    as username  \
    from \
        kimai2_timesheet t \
        inner join kimai2_users u on t.user=u.id \
        inner join kimai2_activities a on t.activity_id=a.id \
        inner join kimai2_projects p on t.project_id=p.id \
        inner join kimai2_customers c on p.customer_id=c.id;

    docker exec -i mysql mysql -B -u root -pchangeme kimai < kimai-dump.sql

    composer require drupal/migrate_source_csv
 
## Dev stuff

    docker run -ti -p 8888:8888 --rm --name timeshite \
        -v $(pwd)/modules:/opt/drupal/web/modules \
        -v $(pwd)/drupal.sqlite:/opt/drupal/web/sites/default/files/.ht.sqlite \
        -e UID=$(id -u) -e GID=$(id -g) \
        tobybatch/timeshite

## Exporting config

Export the views

    drupal config:export:view timesheet_customers --module=timesheet --optional-config=yes --include-module-dependencies=yes

Export the content type

    drupal config:export:content:type --module=timesheet --remove-uuid --remove-config-hash --optional-config

Clear content

    drush devel-generate-terms customers 0 --kill && drush devel-generate-terms project 0 --kill && drush devel-generate-terms activity_types 0 --kill && drush devel-generate-content 0 --kill --types=timesheet_entry
