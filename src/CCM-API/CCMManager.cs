using System;
using System.Collections.Generic;
using System.Linq;
using CCM_API.Security;
using Domain;
using Domain.Security;
using Microsoft.Extensions.Configuration;
using Serilog;

namespace CCM_API
{
    public class CCMManager: BaseManager
    {
        public CCMManager(
            IgniteManager igniteManager,
            SystemManager systemManager,
            IConfiguration configuration) : base(igniteManager, configuration)
        {
            this.systemManager = systemManager;
        }

        private SystemManager systemManager;
        
        public void Bootstrap(int testUsers = 0)
        {
            if (configuration["app:allowBootstrap"] == "true")
            {
                logger.Warning("Bootstraping user data");

                var pwdComplexity = new PasswordComplexity()
                {
                    MinSize = 10,
                    MustContainLetters = true,
                    MustContainNumbers = true,
                    MustContainSymbols = true,
                    MustContainCapLetters = false
                };
                
                systemManager.SetSystemPasswordComplexity(pwdComplexity);


                //var dataStorage = igniteManager.GetDataStorage<Object>();
                
                
                var userDataStorage = igniteManager.GetIgnition().GetOrCreateCache<long, User>("Users");
                var userSeq = igniteManager.GetIgnition().GetAtomicSequence("UserIdSeq", 1, true);
                var accountDataStorage = igniteManager.GetIgnition().GetOrCreateCache<long, Account>("Accounts");
                var accountSeq = igniteManager.GetIgnition().GetAtomicSequence("AccountIdSeq", 1, true);
                var userGroupDataStorage = igniteManager.GetIgnition().GetOrCreateCache<long, UserGroup>("UserGroups");
                var userGroupSeq = igniteManager.GetIgnition().GetAtomicSequence("UserGroupsIdSeq", 2, true);
                var roleDataStorage = igniteManager.GetIgnition().GetOrCreateCache<long, Role>("Roles");
                var roleSeq = igniteManager.GetIgnition().GetAtomicSequence("RoleIdSeq", 2, true);
                var envDataStorage = igniteManager.GetIgnition().GetOrCreateCache<long, Domain.Environment>("Environments");
                var envSeq = igniteManager.GetIgnition().GetAtomicSequence("EnvironmentIdSeq", 0, true);
                
                //var sysDataStorage = igniteManager.GetIgnition().GetOrCreateCache<int, User>("SYS");
                //sysDataStorage.Query(new SqlFieldsQuery("CREATE USER test WITH PASSWORD 'test';"));

                
                
                var admRole = new Role()
                {
                    Id = 1,
                    Name = "Administrator",
                    Claims = new List<BaseClaim>()
                };
                
                admRole.Claims.Add(new AllAccessClaim());
                
                roleDataStorage.Put(admRole.Id, admRole);
                
                var userRole = new Role()
                {
                    
                    Id = 2,
                    Name = "User",
                    Claims = new List<BaseClaim>() 
                };
                
                userRole.Claims.Add(new DefaultUserClaim());
                
                roleDataStorage.Put(userRole.Id, userRole);
                
                var admGroup = new UserGroup()
                {
                    Id = 1,
                    Name = "Administrators",
                };
                admGroup.RolesIds.Add(admRole.Id);
                
                var admAct = new Account()
                {
                    Id = 1,
                    Login = "admin",
                    Password = PasswordTool.GetHashedPassword("admin"),
                    Active = true
                };

                accountDataStorage.Put(admAct.Id, admAct);
                
                var admUser = new User()
                {
                    Id = 1,
                    Name = "Admin",
                    Description = "Default Admin User",
                    AccountId = admAct.Id,
                    Email = "admin@admin.com",
                    Active = true,

                };
                
                userDataStorage.Put(admUser.Id, admUser);

                admGroup.UsersIds.Add(admUser.Id);

                userGroupDataStorage.Put(admGroup.Id, admGroup);
                
                var usersGroup = new UserGroup()
                {
                    Id = 2,
                    Name = "Users",

                };
                
                usersGroup.RolesIds.Add(userRole.Id);
                
               
                var users = new List<User>();
                for (var i = 2; i < 2 + testUsers; i++)
                {
                    var usrAct = new Account()
                    {
                        Id = accountSeq.Increment(),
                        Login = "user" + i,
                        Password = PasswordTool.GetHashedPassword("user123"),
                        Active = true
                    };
                    accountDataStorage.Put(usrAct.Id, usrAct);
                    var user = new User()
                    {
                        Id = userSeq.Increment(),
                        Name = "user" + i,
                        Description = "Test user:" + i,
                        Email = "user" + i + "@user.com",
                        AccountId = usrAct.Id
                    }; 
                    userDataStorage.Put(user.Id, user);
                    users.Add(user);

                    usersGroup.UsersIds.Add(user.Id);
                }

                userGroupDataStorage.Put(usersGroup.Id, usersGroup);


                var envDev = new Domain.Environment()
                {
                    Id = envSeq.Increment(),
                    Name = "Development"
                };
                
                envDataStorage.Put(envDev.Id, envDev);
                
                
                
                
            }
            else
            {
                logger.Warning("Attempt to bootstrap when it is disabled");
            }
        }

        
    }
}