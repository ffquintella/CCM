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
            UserGroupManager groupManager,
            IConfiguration configuration) : base(igniteManager, configuration)
        {
            this.groupManager = groupManager;
        }

        private UserGroupManager groupManager;
        
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
        
        /// <summary>
        /// Gets all the permisions of a specific owner
        /// </summary>
        /// <param name="ownerId">The Id of the permiision Owner</param>
        /// <param name="type">the permission type</param>
        /// <returns>List of permissions</returns>
        public List<Permission> GetOwnerPermissions(long ownerId, PermissionType type)
        {
            var perms = new List<Permission>();

            var queryable =  GetDataStorage().AsCacheQueryable();
            var permsCe = queryable.Where(perm => 
                perm.Value.OwnerId == ownerId && perm.Value.Type == (int)type).ToList();

            if (permsCe.Count == 0) return null;
            
            foreach (var perm in permsCe)
            {
                perms.Add(perm.Value);
            }

            return perms;

        }
        
        public List<Permission> GetGroupsPermissions(UserGroup[] groups, PermissionType type)
        {
            var perms = new List<Permission>();
            foreach (var group in groups)
            {
         
                var gperms = GetGroupPermissions(group.Id, PermissionType.Application);
                if(gperms != null) perms.AddRange(gperms);
            }

            return perms;
        }
        
        /// <summary>
        /// Gets the permissions of a group to a specific permission type
        /// </summary>
        /// <param name="groupId">The id of the group</param>
        /// <param name="type">The permission type</param>
        /// <returns>A list of permissions</returns>
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

        /// <summary>
        /// This method validates if a determined user has a determined permission to a specific object
        /// </summary>
        /// <param name="userId">The id of the user</param>
        /// <param name="objId">The id of the object to check the permission</param>
        /// <param name="objType">The type of the object</param>
        /// <param name="permConsent">The consent of the permission</param> 
        /// <returns></returns>
        public bool ValidateUserObjectPermission(long userId, long objId, PermissionType objType, PermissionConsent permConsent = PermissionConsent.Read)
        {
            
            var groups = groupManager.GetGroupsOfUser(userId);

            var grpIds = new List<long>();
            foreach (var group in groups)
            {
                grpIds.Add(group.Id);
            }
            
            var queryable =  GetDataStorage().AsCacheQueryable();

            return queryable.Any(perm => perm.Value.Type == (int) objType
             && ( perm.Value.AllAccess || (perm.Value.OwnerId == (int) objId && perm.Value.Consent == (int) permConsent )) 
             && grpIds.Contains(perm.Value.GroupId));
            
            //return false;
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