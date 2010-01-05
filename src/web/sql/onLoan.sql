drop table if exists onLoan;

create table onLoan (
	DueDate Date not null default '0000-00-00',
    CallNo varchar(25) not null unique,
    UserId smallint not null,
    primary key(CallNo),
    foreign key (CallNo) references books (CallNo),
    foreign key (UserId) references users (CardNo)
) engine = myisam;

# trigger for modifying the state of the book in the Books table
create trigger onLoanEnter after insert on onLoan
for each row
update books
set state = "On Loan"
where new.callno = callno;

DELIMITER $$
create trigger onLoanExit before delete on onLoan
for each row
begin
    if exists (select * from onHold where CallNo = old.CallNo)
    then
        update books
        set state = "On Hold"
        where old.callno = callno;
        delete from onHold
        where old.callno = callno;        
    else
        update books
        set state = "In Stacks"
        where old.callno = callno;
    end if;
end;
DELIMITER ;