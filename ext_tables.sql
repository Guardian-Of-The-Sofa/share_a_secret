CREATE TABLE tx_shareasecret_domain_model_secret
(
    uid          int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
    pid          int(11)          DEFAULT '0' NOT NULL,

    crdate       int(11) unsigned DEFAULT '0' NOT NULL,
    message      text             DEFAULT ''  NOT NULL,
    index_hash   varchar(255)                 NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

CREATE TABLE tx_shareasecret_domain_model_eventlog
(
    uid     int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
    pid     int(11)          DEFAULT '0' NOT NULL,

    secret  int(11) unsigned,
    date    int(11) unsigned             NOT NULL,
    event   int(11) unsigned NOT NULL,
    message text,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

