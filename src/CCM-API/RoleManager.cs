using System.Collections.Generic;
using System.Linq;
using Apache.Ignite.Core.Cache;
using Apache.Ignite.Linq;
using Domain;
using Microsoft.Extensions.Configuration;
using Serilog;

namespace CCM_API
{
    public class RoleManager: BaseManager
    {
        public RoleManager(
            IgniteManager igniteManager,
            IConfiguration configuration ) : base(igniteManager,configuration) { }
        
        private ICache<long, Role> GetDataStorage()
        {
            return igniteManager.GetIgnition().GetOrCreateCache<long, Role>("Roles");
        }
        
        public List<Role> GetAll()
        {
            var queryable =  GetDataStorage().AsCacheQueryable();
            var rolesCe = queryable.ToList(); //.Where(grp => grp.Key > 0).ToList();

            if (rolesCe.Count == 0) return null;
            
            var roles = new List<Role>();

            foreach (var role in rolesCe)
            {
                roles.Add(role.Value);  
            }
            return roles;
        }


        public Role FindById(long id)
        {
            var storage = GetDataStorage();

            return storage.Get(id);
        }
        
    }
}