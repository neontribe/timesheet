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
    inner join kimai2_projects p on a.project_id=p.id \
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

Grab a list of projects, we'll need to match these up against trello boards:

```bash
    docker exec -i kimai00_mysql_1 mysql -B -u root -pchangeme kimai -e "select name from kimai2_projects" > projects.txt
```

You will then need to noramlise the data ready for our install.  This assumes you have added LDAP auth to this drupal.

```bash
    .fixtures/convert-and-map.sh
```


