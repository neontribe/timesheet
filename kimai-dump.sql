select 
    t.id, u.username, a.name, t.start_time, t.duration, t.description, p.name, c.name 
    from kimai2_timesheet t 
    inner join kimai2_users u on t.user=u.id 
    inner join kimai2_activities a on t.activity_id=a.id 
    inner join kimai2_projects p on t.project_id=p.id 
    inner join kimai2_customers c on p.customer_id=c.id
    INTO OUTFILE '/tmp//export.csv' 
    FIELDS TERMINATED BY ','
    ENCLOSED BY '"'
    LINES TERMINATED BY '\n';
