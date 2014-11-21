create table runalyze_hr_zones (id int, start int, end int);
insert into runalyze_hr_zones (id, start, end) values (0,0,50);
insert into runalyze_hr_zones (id, start, end) values (1,50,60);
insert into runalyze_hr_zones (id, start, end) values (2,60,70);
insert into runalyze_hr_zones (id, start, end) values (3,70,80);
insert into runalyze_hr_zones (id, start, end) values (4,80,90);
insert into runalyze_hr_zones (id, start, end) values (5,90,100);
create table runalyze_training_hr_zones (accountid int(11) NOT NULL, id_training int(11) NOT NULL, id_zone int, `s` decimal(8,2), distance decimal(8,2));

create table runalyze_training_pace_zones (accountid int(11) NOT NULL, id_training int(11) NOT NULL, id_zone int, `s` decimal(8,2), distance decimal(8,2), avghr int);


drop table runalyze_training_hr_zones;
drop table runalyze_training_pace_zones;
