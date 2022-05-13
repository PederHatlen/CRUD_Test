create database crud;

use crud;

create table users(
    uuid varchar(255) not null primary key,
    ip varchar(15) not null,
    color varchar(7) not null
);

create table messages(
    id int not null primary key AUTO_INCREMENT,
    msg varchar(255) not null,
    uuid varchar(255) not null,
    time datetime DEFAULT current_timestamp,
    Foreign key (uuid) REFERENCES users(uuid)
);