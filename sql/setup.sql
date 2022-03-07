create database crud;

use database crud;

create table messages(
    id int not null primary key AUTO_INCREMENT,
    message varchar(255) not null,
    user string(255) not null,
    sent_at datetime DEFAULT current_timestamp
);
