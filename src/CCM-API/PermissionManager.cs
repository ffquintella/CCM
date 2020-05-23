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
            var permsCe = queryable.Where(app => app.Value.Type == (int)PermissionType.Application 
            && app.Value.OwnerId == appId && app.Value.GroupId == groupId ).ToList();

            if (permsCe.Count == 0) return null;
            
            var perms =new List<Permission>();

            foreach (var perm in permsCe)
            {
                perms.Add(perm.Value);
            }

            return perms;
        }

        public Permission GetAllAccessPermission(long groupdid, PermissionType type)
        {
            return new Permission()
            {
                AllAccess = true,
                Type = (int)type,
                Consent = (int) PermissionConsent.Write,
                Id = -1,
                GroupId = groupdid

            };
        }
        
        public List<Permission> GetGroupsPermissions(UserGroup[] groups, PermissionType type)
        {
            var perms = new List<Permission>();
            foreach (var group in groups)
            {
                //Admins can read them all
                if (group.RolesIds.Contains(1))
                {
                    var allperm = new List<Permission>();
                    allperm.Add(GetAllAccessPermission(group.Id, type));
                    return allperm;
                }
                
                var gperms = GetGroupPermissions(group.Id, PermissionType.Application);
                if(gperms != null) perms.AddRange(gperms);
            }

            return perms;
        }
        
        public List<Permission> GetGroupPermissions(long groupId, PermissionType type)
        {
            var queryable =  GetDataStorage().AsCacheQueryable();
            var permsCe = queryable.Where(perm =>  perm.Value.GroupId == groupId && perm.Value.Type == (int)PermissionType.Application).ToList();

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
            var permsCe = queryable.Where(app => app.Value.Type == (int)PermissionType.Application 
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