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
            var perms = new List<Permission>();
            var apps = new List<Application>();
            foreach (var group in groups)
            {
                //Admins can read them all
                if (group.RolesIds.Contains(1))
                {
                    return  GetAll();
                }
                //TODO: Implemnte filtered search
                var gperms = permManager.GetGroupPermissions(group.Id, PermissionType.Application);
                if(gperms != null) perms.AddRange(gperms);
            }
            
          
            return null;
        }
    }

  }