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

```
    tobias@tobias:ts $ drupal config:export:view --module=timesheet --optional-config 
    View to be exported [Archive]:
    > timesheets

    Export view in module as an optional configuration (yes/no) [yes]:
    > yes

    Include view module dependencies in module info YAML file (yes/no) [yes]:
    > yes

    [+] The following module dependencies were included at "modules/custom/timesheet/timesheet.info.yml"
       [-] csv_serialization
       [-] datetime
       [-] duration_field
       [-] node
       [-] rest
       [-] serialization
       [-] user
       [-] views_data_export
    commands.views.export.messages.view-exported
    - modules/custom/timesheet/config/optional/views.view.timesheets.yml
    - modules/custom/timesheet/config/optional/core.entity_view_mode.node.teaser.yml
    - modules/custom/timesheet/config/optional/field.storage.node.field_activity_type.yml
    - modules/custom/timesheet/config/optional/field.storage.node.field_date.yml
    - modules/custom/timesheet/config/optional/field.storage.node.field_project.yml
    - modules/custom/timesheet/config/optional/field.storage.node.field_time_spent.yml
    - modules/custom/timesheet/config/optional/field.storage.node.field_user.yml
    - modules/custom/timesheet/config/optional/node.type.timesheet_entry.yml
```

Export the content type

    drupal config:export:content:type --module=timesheet --remove-uuid --remove-config-hash --optional-config

Clear content

    drush devel-generate-terms customers 0 --kill && drush devel-generate-terms project 0 --kill && drush devel-generate-terms activity_types 0 --kill && drush devel-generate-content 0 --kill --types=timesheet_entry
