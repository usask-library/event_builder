# Event Builder

## Overview

Event Builder allows for the creation of events involving people, places and objects from specific sections of TEI
documents.

Container elements (`item`, `seg`, `p` or `div`) allow the TEI document to be broken into selectable sections,
and people, places and objects to be extracted from each section. Acquisition, production, observation and 
production events can then be constructed from that extracted data.

## Installation

Detailed install instructions can be [found here](INSTALL.md)

## User Management

The event builder and event details are available to all users of the system.  Only users with accounts can create
events, however.   Accounts must be create by a system administrator using a command line tool.

To create and account, run the following commands

```bash
cd /var/www/event_builder
php artisan help user:create
```

This process will ask for a user name, email address and password.  Once the account has been created, the user may
log on to the system using the **Login** link in the upper right corner of the Event Builder web interface.


## Structure

### Document Location

Event Builder will look for XML/TEI documents in the `public/XML` folder.

### Document Structure

Event Builder assumes the TEI documents contain markup that has been formatted in a particular way.

- Document sections from which one or more events should have a container element such as a `item`, `seg`, `p` or `div`
- Within those sections
  - People should be identified by a `name` element whose `type` attribute is `person` and whose `ref` attribute is a
    numeric identifier unique to that person. That same identifier should be used for all occurrences of that person
    across all TEI documents.  This identifier should match the `id` used in the `people` table of the Event Builder
    database.
    ```xml
    <name type="person" ref="30">John Bargrave</name>
    ```
  - Places should be identified by a `name` element whose `type` attribute is `place` and whose `ref` attribute is a
    numeric identifier unique to that place. That same identifier should be used for all occurrences of that place
    across all TEI documents.  This identifier should match the `id` used in the `places` table of the Event Builder
    database.
    ```xml
    <name type="place" ref="52">London</name>
    ```
  - Objects should be identified by a `seg` element whose `type` attribute is `object`, `objectSet`, `objectGroup`,
    `collection`, `coin` or `medal` and whose `xml:id` attribute is (typically) an identifier unique to the document.
    This identifier should match the `identifier` used in the `item_identifier` table of the Event Builder
    database.
    ```xml
    <seg type="object" xml:id="BarCat00170">
      (16). The <name type="place" ref="974">River of Tyber</name>,
      carved on a piece of <rs type="object">coral</rs>; ancient.
    </seg>
    ```
    The structure of the documents allow for these object definitions to be nested
    ```xml
    <item xml:id="BarCatITEM00390" n="39">
      <p>
        <seg type="objectSet" xml:id="BarCat00400">(39).
          <seg type="object" xml:id="BarCat004002">A pretty little <rs type="object">padlock</rs></seg>
          <seg type="object" xml:id="BarCat004004">and <rs type="object">key</rs> of guilt mettle</seg>
        </seg>, and
        <seg type="object" xml:id="BarCat00405"> a piece of <rs type="object">coral</rs>,
          given me by <name type="person" ref="4541">a nunn</name>, -- whose guifts are commonly costly,
          for you must return the double.
        </seg>
      </p>
    </item>
    ```
  - Dates should be identified by a `date` element whose `when` attribute is a date of the form `YYYY-MM-DD`, `YYYY-MM` or
    `YYYY`
    ```xml
    <date when="1662-01" period="cont">January 1662</date>
    ```
  

## User Manual

Detailed instructions on using the Event Builder interface are not yet available.
