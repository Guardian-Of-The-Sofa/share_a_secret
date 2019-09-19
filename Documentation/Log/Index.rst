.. include:: ../Includes.txt


.. _logger:

==============
Logger
==============

The extension also comes with a simple event logger.
The events are logged into the database **tx_shareasecret_domain_model_eventlog**.
The following events are logged:

* Create
   When a new secret message was created

* Delete
   When someone deleted a message

* Request
   When someone tries to access a message via a link

* Success
   When someone successfully entered a password to a message

* Not Found
   When someone tried to access a non existing message.
   This can happen in the following cases:

   * Someone tried to access a message which has already been deleted
   * Someone entered a wrong password for a message

At the time of writing (Sep. 17, 2019) the database can only be viewed
with an external database application.
In a future version of the extension you will be able to view
the log information in the Typo3 backend.

