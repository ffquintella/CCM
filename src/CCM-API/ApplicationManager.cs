using System.Collections.Generic;
using System.Linq;
using Apache.Ignite.Core.Cache;
using Apache.Ignite.Linq;
using Domain;
using Microsoft.Extensions.Configuration;

namespace CCM_API
{
    public class ApplicationManager: BaseManager
    {
        public ApplicationManager(
            IgniteManager igniteManager,
            PermissionManager permissionManager,
            UserGroupManager userGroupManager,
            IConfiguration configuration) : base(igniteManager, configuration)
        {
            permManager = permissionManager;
            groupManager = userGroupManager;
        }

        private PermissionManager permManager;
        private UserGroupManager groupManager;
        
        private ICache<long, Application> GetDataStorage()
        {
            return igniteManager.GetIgnition().GetOrCreateCache<long, Application>("Applications");
        }
        

        public List<Application> GetUserApps(long userAccountId)
        {
            var groups = groupManager.GetGroupsOfUser(userAccountId);

            var apps = GetGroupsApps(groups);
          
            return apps;
        }

        public List<Application> GetAll()
        {
            var queryable =  GetDataStorage().AsCacheQueryable();
            var appsCe = queryable.ToList(); 

            if (appsCe.Count == 0) return null;
            
            var apps = new List<Application>();

            foreach (var app in appsCe)
            {
                apps.Add(app.Value);  
            }
            return apps;
        }
        public List<Application> GetGroupsApps(UserGroup[] groups)
        {
            var apps = new List<Application>();
            var perms = permManager.GetGroupsPermissions(groups, PermissionType.Application);

            // If we have the getall permission return all items
            if (perms.Where(perm => perm.AllAccess == true).Count() > 0) return GetAll();
            

            var appsIds = new List<long>();

            foreach (var perm in perms)
            {
                appsIds.Add(perm.OwnerId);
            }

            var queryable =  GetDataStorage().AsCacheQueryable();
            var appsCe = queryable.Where(app => appsIds.Contains(app.Key)).ToList();

            foreach (var app in appsCe)
            {
                apps.Add(app.Value);  
            }
            return apps;
          
        }
    }

  }