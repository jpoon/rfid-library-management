drop table if exists onHold;

create table onHold (
    CallNo varchar(25) not null unique,
    UserId smallint not null,
    primary key(CallNo),
    foreign key (CallNo) references books (CallNo),
    foreign key (UserId) references users (CardNo)
) engine = myisam;