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
            IConfiguration configuration ) : base(igniteManager,configuration) { }
        
        private ICache<long, Application> GetDataStorage()
        {
            return igniteManager.GetIgnition().GetOrCreateCache<long, Application>("Applications");
        }
        
        // Get all apps the user has right to see
        public List<Application> GetUserApps()
        {
            var queryable =  GetDataStorage().AsCacheQueryable();
            var appsCe = queryable.ToList(); //.Where(grp => grp.Key > 0).ToList();

            if (appsCe.Count == 0) return null;
            
            var apps = new List<Application>();

            /*foreach (var app in appsCe)
            {
                apps.Add(app.Value);  
            }*/
            
            return apps;
        }
    }

  }