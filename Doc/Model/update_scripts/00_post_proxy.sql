drop table if exists POST_PROXY;

/*==============================================================*/
/* Table: POST_PROXY                                            */
/*==============================================================*/
create table POST_PROXY
(
   FLAG_PROXY           VARCHAR(1) not null default 'N',
   PROXY_HOST           VARCHAR(64),
   PROXY_PORT           VARCHAR(4),
   PROXY_USER           VARCHAR(64),
   PROXY_PASSWD         VARCHAR(64),
   LM_DATE              DATETIME not null,
   primary key (FLAG_PROXY)
);

INSERT INTO POST_PROXY (FLAG_PROXY, PROXY_HOST, PROXY_PORT, PROXY_USER, PROXY_PASSWD, LM_DATE) 
VALUES ('N', NULL, NULL, NULL, NULL, '2013-10-26 00:00:00');
