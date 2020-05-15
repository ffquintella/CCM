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
        

        public List<Application> GetUserApps(long userId)
        {
            var groups = groupManager.GetGroupsOfUser(userId);

            var apps = GetGroupsApps(groups);
          
            return apps;
        } 
        
        public List<Application> GetGroupsApps(UserGroup[] groups)
        {
            var perms = new List<Permission>();
            foreach (var group in groups)
            {
                var gperms = permManager.GetGroupPermissions(group.Id);
                if(gperms != null) perms.AddRange(gperms);
            }
            
          
            return null;
        }
    }

  }