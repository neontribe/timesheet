# Timesheet

Module to add time sheeting to drupal 8

## Testing with docker

### Throw away demo

Runs with the standard drupal look and feel, no pre-configured fixtures, menus etc.  Does not persist data.

    docker build -t timeshite .
    docker run -ti --rm --name timeshite -p 8888:8888 timeshite

Run a persistent data version using mysql and saved files folder.

    docker-compose up --build -d
    docker-compose logs

## Importing from kimai

Run this SQL against the kimai to dump out the existsing kimai data.

```bash
cat <<EOF > kimai-dump.sql
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
EOF
```

Now run that as a batch export and trim of the header line (TODO there is probably a mysyl command to suppress the headers).  You will need to adjust the SQL details to match your setup.

```bash
    mysql -B -u root -pchangeme kimai < kimai-dump.sql > export.orig.csv
```

To run this against a container sql:

```bash
    docker exec -i mysql mysql -B -u root -pchangeme kimai < kimai-dump.sql > export.orig.csv
```

You will then need to noramlise the data ready for our install.  This assumes you have added LDAP auth to this drupal.

```bash
    .fixtures/convert-and-map.sh
```

### These commands are native to our install

    docker exec -ti timeshite composer --working-dir=/opt/drupal require drupal/bootstrap
    docker exec -ti timeshite ../vendor/bin/drupal theme:install bootstrap
    docker exec -ti timeshite drush config-set system.theme default bootstrap

    docker exec -ti timeshite composer --working-dir=/opt/drupal require drupal/ldap
    docker exec -ti timeshite drush en -y ldap ldap_authentication ldap_user ldap_query ldap_servers

 
## Dev stuff

### Exporting config

Export the views

    drupal config:export:view timesheet_customers    --module=timesheet --optional-config=yes --include-module-dependencies=yes
    drupal config:export:view timesheet_aggregations --module=timesheet --optional-config=yes --include-module-dependencies=yes
    drupal config:export:view timesheet_activities   --module=timesheet --optional-config=yes --include-module-dependencies=yes

Export the content type

    drupal config:export:content:type --module=timesheet --remove-uuid --remove-config-hash --optional-config

### Clear content

    drush devel-generate-terms customers 0 --kill && drush devel-generate-terms project 0 --kill && drush devel-generate-terms activity_types 0 --kill && drush devel-generate-content 0 --kill --types=timesheet_entry
