Light_MicroPermission, conception notes
=================
2019-09-26 -> 2019-12-18



I'm trying to make a permission system that makes sense for the soon to come [Light_Kit_Admin](https://github.com/lingtalfi/Light_Kit_Admin) plugin (maybe as you read those lines it has come out already?).


I've read the [permissions conception notes](https://github.com/lingtalfi/Light_User/blob/master/doc/pages/permission-conception-notes.md),
and they make sense, but they tend to create a lot of permissions.

The problem with the current state of the permission implementation is that each permission is stored in the session.
So each permission is one more entry in the $_SESSION array, and I'm not a big fan of having too big session arrays, 
so I want to find another solution.

In particular, when we have permissions related to sql tables: if each table spawns three or four permissions:

- create 
- read
- update
- delete

Considering a plugin like [Ekom](https://github.com/KamilleModules/Ekom) (my last e-commerce plugin for the kamille framework),
which has more than a hundred tables, just for one plugin, we would populate the session array quite rapidly.


So here is my alternative solution: micro permission.





How does it work?
-------------------

The micro-permission system basically consists of a map of micro-permission names to permissions (as defined in the [permission conception notes](https://github.com/lingtalfi/Light_User/blob/master/doc/pages/permission-conception-notes.md)).

Something like this:

- tables.lud_user.create:
    - Light_Kit_Admin.admin
- tables.lud_user.read:
    - Light_Kit_Admin.admin
    - Light_Kit_Admin.user
    - PluginABC.permission123
- my_micro_permission_456:
    - my_permission_789
- ...


There is a micro-permission handler (that we provide), which basically holds that map and provides the hasMicroPermission method:


- hasMicroPermission ( string microPermission ): bool






Namespaces
---------------

By convention, a micro-permission name is a dot separated string, where the first component is called the namespace.

That's because as we said, the micro-permission system is used for when we have a lot of permissions to deal with, and
so more often than not we can group those in namespaces.


We can disable namespaces temporarily, which can be useful sometimes.
For instance, we have this [recommended micro-permission notation for database interaction](https://github.com/lingtalfi/Light_MicroPermission/blob/master/doc/pages/recommended-micropermission-notation.md#database-interaction), 
which has the "tables" namespace, and during a plugin A installation phase (assuming plugin A installs tables in the database),
we can temporarily disable the "tables" namespace to allow the plugin A to install itself.


In other words, the micro-permission system is aimed towards the current user, but we can disable it temporarily 
when the executing actions on the behalf of the developer or plugin author.  



Building the map
-----------

The map is built by plugins and/or the human administrator.

It's worth understanding the importance of the human administrator role.

Plugins authors try their best to provide the most accurate map bits, so that when the end user installs a plugin,
the micro-permissions are already handled.

However, sometimes it's not always possible.

To understand that, let's distinguish between two kinds of plugins:

- the ones who create their own permissions
- the ones who don't 


An example of plugin who creates its own permissions is **Light_Kit_Admin**, which creates permissions such as:

- Light_Kit_Admin.user
- Light_Kit_Admin.admin


An example of plugin who doesn't create its own permission is [Light_UserData](https://github.com/lingtalfi/Light_UserData).
However, the **Light_UserData** plugin creates tables in the database.
And this is a common scenario. 

Now because the **Light_UserData** plugin provides tables, it means that there will be micro-permissions for that table.

For instance the **Light_UserData** provides the following tables:

- luda_directory_map
- luda_resource
- ...

And so we will need micro-permissions to cover those tables.

I created a [recommended notation for micro-permission related to database tables](https://github.com/lingtalfi/Light_MicroPermission/blob/master/doc/pages/recommended-micropermission-notation.md#database-interaction),
and so if we use it we would need the following micro-permissions:

- tables.luda_directory_map.create 
- tables.luda_directory_map.read 
- tables.luda_directory_map.update 
- tables.luda_directory_map.delete
- tables.luda_resource.create 
- tables.luda_resource.read 
- tables.luda_resource.update 
- tables.luda_resource.delete
- ...


But as you can guess, the **Light_UserData** plugin is unable to bind those micro-permissions by itself, because it doesn't have
the knowledge of what permission it should bind them to. 

And so my point is that the micro-permission bindings (the one that provide the bits that makes the map) are only 
those plugins who create their own permissions.

And because the plugin author of such a plugin (let's call it plugin A) doesn't know the future, at some point in time their might be plugins
that require micro-permission bindings that are not defined in the plugin A.

Now of course, ideally the plugin A author will find out about those and incorporate the micro-permission bindings in his plugin.

But if he doesn't, we need a way to do it manually.

And so our handler provides a method to do that, which is the **registerMicroPermissionsByFile** method.

By the way, this is also the same method that's used by plugins to register their micro-permission bindings.

So all that long discussion was just about how important the role of the human administrator can be in some cases.  


 










