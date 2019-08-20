CREATE TABLE tx_hnsharesecret_domain_model_secret
(
    uid          int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
    pid          int(11)          DEFAULT '0' NOT NULL,

    message      text             DEFAULT ''  NOT NULL,
    index_hash   varchar(255)                 NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

