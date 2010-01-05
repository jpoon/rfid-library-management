 drop table if exists userSearchList;

 create table userSearchList (
	BookID bigint not null unique
 ) engine = myisam;
