using System.Collections.Generic;
using System.Linq;
using Apache.Ignite.Core.Cache;
using Apache.Ignite.Linq;
using Domain;
using Microsoft.Extensions.Configuration;

namespace CCM_API
{
    public class PermissionManager: BaseManager
    {
        
        public PermissionManager(
            IgniteManager igniteManager,
            IConfiguration configuration ) : base(igniteManager,configuration) { }
        
        private ICache<long, Permission> GetDataStorage()
        {
            return igniteManager.GetIgnition().GetOrCreateCache<long, Permission>("Permissions");
        }

        public List<Permission> GetGroupAppPermissions(long appId, long groupId)
        {
            var queryable =  GetDataStorage().AsCacheQueryable();
            var permsCe = queryable.Where(app => app.Value.Type == PermissionType.Application 
            && app.Value.OwnerId == appId && app.Value.GroupId == groupId ).ToList();

            if (permsCe.Count == 0) return null;
            
            var perms =new List<Permission>();

            foreach (var perm in permsCe)
            {
                perms.Add(perm.Value);
            }

            return perms;
        }
        
        public List<Permission> GetGroupPermissions(long groupId)
        {
            var queryable =  GetDataStorage().AsCacheQueryable();
            var permsCe = queryable.Where(app => app.Value.Type == PermissionType.Application 
                                                 && app.Value.GroupId == groupId ).ToList();

            if (permsCe.Count == 0) return null;
            
            var perms =new List<Permission>();

            foreach (var perm in permsCe)
            {
                perms.Add(perm.Value);
            }

            return perms;
        }
        
        public List<Permission> GetAppPermissions(long appId)
        {
            var queryable =  GetDataStorage().AsCacheQueryable();
            var permsCe = queryable.Where(app => app.Value.Type == PermissionType.Application 
            && app.Value.OwnerId == appId ).ToList();
            if (permsCe.Count == 0) return null;
            
            var perms =new List<Permission>();

            foreach (var perm in permsCe)
            {
                perms.Add(perm.Value);
            }

            return perms;
        }
        
    }
}