# Wordpress Logging

## CHANGELOG

### 0.0.0.0 - 25.06.2018

- Added the LogInterface, which tells which methods to be implemented for a log
- Added the 'LogPost' class, which will be used as a post wrapper and a logging object alike.
Maps the logging functionality of 'LogInterface' to a wordpress log post
- Added the 'LogPostRegistration' which is a method wrapper for the LogPost register method, 
that registers the new CPT with wordpress at the beginning.

### 0.0.0.1 - 25.06.2018 

- Fixed the bug with log lines for the LogPost containing commas being interpreted as a listing 
of many post terms...

### 0.0.0.2 - 27.06.2018

- Removed the custom taxonomy being registered with wordpress. The log messages are now being stored in a 
array like structure of post meta instead of taxonomy terms.
- Complete documentation of classes

### 0.0.0.3 - 27.06.2018

- Changed the way the log messages were being fetched to be displayed in the admin dashboard meta box from the 
taxonomy terms to the post meta array

### 0.0.0.4 - 27.06.2018

- Removed the 'custom post meta' from the list of supported widgets in the edit screen within the admin 
area, because since the log messages are now being stored as separate post meta elements, this widget would 
become very crowded
- Added an additional $subject parameter to the constructor of the LogPost class, so that a more descriptive 
title for the log post can optionally be specified.

### 0.0.0.5 - 28.06.2018

- Modified the log data meta box, that display the actual log to also display the line number in front of each line

### 0.0.0.6 - 28.06.2018

- Added the 'menu_icon' to the arguments of the log post type registration 

### 0.0.0.7 - 08.08.2018

- The line numbers in front of every log message, when displayed in the wordpress backend now always consist of 4 
4 digits with leading zeros.
- Added AJAX functionality so that LogPost pages in the admin panel load the new log lines without reloading.

### 0.0.0.8 - 15.08.2018

- Added a new class 'VoidLog', which implements the 'LogInterface': It does nothing. All its methods are just 
stubs. Log messages passed to those objects will just disappear.

### 0.0.0.9 - 17.10.2018

- Added the package [wp-cpt-lib](https://github.com/the16thpythonist/wp-cpt-lib.git) To the composer requirements. It is 
essentially a base package for introducing new custom post types in a very object oriented way.