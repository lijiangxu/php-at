# Asynchronous Tasks #
## This application runs tasks in separate processes ##


Usage **./run `[--id TaskID]`**

_If **id** not specified all tasks will be executed._

```

$> ./run

08/14 05:18:08 Start pool
08/14 05:18:08 Task #1 - Started
08/14 05:18:08 Task #2 - Started
08/14 05:18:08 Task #3 - Started
....................

Task #2 output:
Called doSomth2

08/14 05:18:29 Task #2 - Done
08/14 05:18:29 Task #4 - Started
...................

Task #3 output:
Called doSomth3

08/14 05:18:49 Task #3 - Done
08/14 05:18:49 Task #5 - Started

Task #4 output:
Called doSomth4

08/14 05:18:50 Task #4 - Done
08/14 05:18:50 Task #6 - Started
..................

Task #1 output:
Called doSomth1

08/14 05:19:09 Task #1 - Done

Task #5 output:
Called doSomth5

08/14 05:19:10 Task #5 - Done

Task #6 output:
Called doSomth6

08/14 05:19:11 Task #6 - Done
08/14 05:19:11 Pool is done. Successful: 6. Failed: 0.
```