

============ Instructions ============
1. Source "db_events.sql" in mysql session while logged in as a user who can create databases and give grant permissions.  This will create the database, table and user with approriate permissions.
2. Create a user that has full privileges on the database db_events
	Ex.  grant all on db_events.* to 'event'@'localhost' identified by 'password';
3. Set the config values in config.php
4. Browse the "index.php" file on the browser.

Lookup tables:
1. referrals - used to populate the source drop down list.
2. newssite - if "News Website" is selected then this table is used to populate the resulting second drop down list.
3. states - used to verify a valid state abbreviation.



