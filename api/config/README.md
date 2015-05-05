# Config Directory

This directory contains all configuration files for the API. To ensure files are
loaded in the correct order all files are prepended with a priority. If you
follow the following guidelines configuration issues will be limited to a
minimum.

## Priorities
### 10-
There are two important priorities. The first is priority 10 or lower. Every
file prepended with this number should be part of the very core of the API.
Examples of such settings are: debugging level, maintenance mode, and logging.

### 99-
Priority 99 is reserved for environment specific settings. This could be both
production environment or development environment settings. Some may be related
to the system. While others are more related to the functionallity of the
application itself. i.e. How much points one should get for answering a 
question.
*note: Files prepended with the 99 priority are ignored by GIT*

### others
Between 10 and 90 every priority is allowed. One should keep a logical structure
and resort to multiples of ten as much as possible.
