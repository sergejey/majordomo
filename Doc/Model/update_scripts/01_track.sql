drop table if exists POST_TRACK;

/*==============================================================*/
/* Table: POST_TRACK                                            */
/*==============================================================*/
create table POST_TRACK
(
   TRACK_ID             VARCHAR(14) not null,
   TRACK_NAME           VARCHAR(64) not null,
   FLAG_CHECK           VARCHAR(1) not null default 'Y',
   TRACK_DATE           DATETIME not null,
   LM_DATE              DATETIME not null,
   primary key (TRACK_ID)
);
    
drop table if exists POST_TRACKINFO;

/*==============================================================*/
/* Table: POST_TRACKINFO                                        */
/*==============================================================*/
create table POST_TRACKINFO
(
   TRACK_ID             VARCHAR(14) not null,
   OPER_DATE            DATETIME not null,
   OPER_TYPE            INT(10) not null,
   OPER_NAME            VARCHAR(64) not null,
   ATTRIB_ID            INT(10),
   ATTRIB_NAME          VARCHAR(64),
   OPER_POSTCODE        INT(10),
   OPER_POSTPLACE       VARCHAR(64) not null,
   ITEM_WEIGHT          DECIMAL(10,6),
   DECLARED_VALUE       DECIMAL(10,6),
   DELIVERY_PRICE       DECIMAL(10,6),
   DESTINATION_POSTCODE INT(10),
   DELIVERY_ADDRESS     VARCHAR(255),
   LM_DATE              DATETIME not null,
   primary key (TRACK_ID, OPER_DATE)
);

alter table POST_TRACKINFO add constraint FK_POST_TRACKINFO__TRACK_ID foreign key (TRACK_ID)
      references POST_TRACK (TRACK_ID) on delete restrict on update restrict;

