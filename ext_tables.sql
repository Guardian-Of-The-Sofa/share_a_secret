CREATE TABLE tx_hnsharesecret_domain_model_secret
(
    uid           int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
    pid           int(11)          DEFAULT '0' NOT NULL,

    message       text             DEFAULT ''  NOT NULL,
    password_hash varchar(255)                 NOT NULL,
    link_hash     varchar(255)                 NOT NULL,
    attempt       int(11) unsigned DEFAULT '0' NOT NULL,
    last_attempt  int(11) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

CREATE TABLE tx_hnsharesecret_domain_model_credential
(
    uid           int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
    pid           int(11)          DEFAULT '0' NOT NULL,

    company       varchar(255)     DEFAULT ''  NOT NULL,
    username      varchar(255)                 NOT NULL,
    password_hash varchar(255)                 NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);
