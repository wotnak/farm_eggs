# farmOS Eggs

Provides functionalities for tracking egg harvests.

This module is an add-on for the [farmOS](http://drupal.org/project/farm)
distribution.

## Features

- Quick form for recording egg harvests.
- Option to mark animal/group assets as producing eggs.
- Option to record quantities per egg types in single egg harvest.
- Two egg harvest recording workflows:
  - simple (default) - total egg quantity required, quantities per egg type optional,
  - detailed - quantities per egg type required, total egg quantity calculated automatically.

## Installation

Install as you would normally install a contributed drupal module. See:
<https://www.drupal.org/docs/extending-drupal/installing-modules> for further
information.

## Configuration

- Select desired workflow in settings (/farm/settings/eggs).
- Mark animal/group assets as producing eggs.
  - In the asset edit form check 'Produces eggs' option.
- Define some egg types (/admin/structure/taxonomy/manage/egg_type/overview).

## Usage

- Open egg harvest quick form. In menu Quick forms > Eggs.
- Depending on the selected workflow:
  - simple:
    - provide total egg quantity in the `Quantity` field,
    - select group/animal the egg harvest is from,
    - optionally provide sub total quantities per egg type in the `Egg types` section,
  - detailed:
    - select group/animal the egg harvest is from,
    - provide sub total quantities per egg type in the `Egg types` section.
- Optionally change harvest datetime in the `Timestamp` field.
- Optionally add some notes in the `Notes` field.
- Save changes using `Submit` button at the top of the form.
