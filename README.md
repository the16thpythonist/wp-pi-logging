# Wordpress Logging

## CHANGELOG

### 0.0.0.0 - 25.06.2018

- Added the LogInterface, which tells which methods to be implemented for a log
- Added the 'LogPost' class, which will be used as a post wrapper and a logging object alike.
Maps the logging functionality of 'LogInterface' to a wordpress log post
- Added the 'LogPostRegistration' which is a method wrapper for the LogPost register method, 
that registers the new CPT with wordpress at the beginning.
