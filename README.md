# Wordpress Logging

## CHANGELOG

### 0.0.0.0 - 25.06.2018

- Added the LogInterface, which tells which methods to be implemented for a log
- Added the 'LogPost' class, which will be used as a post wrapper and a logging object alike.
Maps the logging functionality of 'LogInterface' to a wordpress log post
- Added the 'LogPostRegistration' which is a method wrapper for the LogPost register method, 
that registers the new CPT with wordpress at the beginning.

## 0.0.0.1 -25.06.2018 

- Fixed the bug with log lines for the LogPost containing commas being interpreted as a listing 
of many post terms...

## 0.0.0.2 - 27.06.2018

- Removed the custom taxonomy being registered with wordpress. The log messages are now being stored in a 
array like structure of post meta instead of taxonomy terms.
- Complete documentation of classes

# 0.0.0.3 - 27.06.2018

- Changed the way the log messages were being fetched to be displayed in the admin dashboard meta box from the 
taxonomy terms to the post meta array

