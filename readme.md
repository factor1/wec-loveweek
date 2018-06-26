# Waters Edge Church Love week
A custom Zend Application for tracking volunteer positions and users for LoveWeek events


### Tools / Packages Used
- [Sass](http://sass-lang.com/)


### Production Repositories

The **production** repository can be found at:
```
ssh://watersed@209.59.138.44:2222/home/watersed/repos/loveweek.git
```

## Contact
For repository questions or issues contact [Matt Adams](mailto:matt@factor1studios.com) or
[factor1](http://factor1studios.com).


## Notes 
### CountDown clock settings. 

The header clock countdown tool is driven by a global controller abstract
```
/application/default/controllers/ControllerAbstract.php
```

It is a 24 hour clock with a date. 
```
$timeEnd = mktime(15, 0, 0, 8, 10, 2016);
```
Format: mktime(24h, min, sec, M, D, Y);



### Event Arguments
Event arguments are located in 2 places. One front end, the other admin backend.  This controls the event location, task type, and age restrictions. Both files should have the same arguments. 
 ```
/application/default/controllers/EventsController.php
/application/admin/controllers/EventsController.php
```

