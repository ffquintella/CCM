using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Apache.Ignite.Core.Cache;
using Apache.Ignite.Core.DataStructures;
using Apache.Ignite.Linq;
using CCM_API.Exceptions;
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

        private IAtomicSequence GetSequence()
        {
            return igniteManager.GetIgnition().GetAtomicSequence("ApplicationIdSeq", 0, true);
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

        /// <summary>
        /// Creates a new application and assing a new Id to it
        /// </summary>
        /// <param name="app"></param>
        /// <returns></returns>
        /// <exception cref="InvalidParametersException"></exception>
        public async Task<Application> Create(Application app)
        {
            if (app == null) throw new  InvalidParametersException("ApplicationManager", "Create");
            app.Id = GetSequence().Increment();
            var stor = GetDataStorage();
            await stor.PutAsync(app.Id, app);
            return app;
        }

        /// <summary>
        /// Updates an application. The only thing that can't be changed is the id
        /// </summary>
        /// <param name="app"></param>
        /// <returns></returns>
        /// <exception cref="InvalidParametersException"></exception>
        public async Task<Application> Update(Application app)
        {
            if (app == null) throw new  InvalidParametersException("ApplicationManager", "Update");
            var stor = GetDataStorage();
            await stor.PutAsync(app.Id, app);
            return app;
        }
        
      
        public Application GetApp(long id)
        {
            var queryable =  GetDataStorage().AsCacheQueryable();
            var appR = queryable.Where(appR => appR.Key == id).First();
            if (appR != null) return appR.Value;
            return null;
        }

        public Application GetUserApp(long userId,  long appId)
        {
            var hasPermission =
                permManager.ValidateUserObjectPermission(userId, appId, PermissionType.Application,
                    PermissionConsent.Read);
            if(!hasPermission) throw new NoPermissionException(userId);
            
            var queryable =  GetDataStorage().AsCacheQueryable();
            var appsCe = queryable.Where(app => app.Key == appId).FirstOrDefault();
            return appsCe.Value;
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