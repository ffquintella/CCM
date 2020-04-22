using System.Collections.Generic;
using System.Linq;
using Apache.Ignite.Core.Cache;
using Apache.Ignite.Core.DataStructures;
using Apache.Ignite.Linq;
using Domain;
using Microsoft.Extensions.Configuration;
using Serilog;
using Apache.Ignite.Linq;
using Domain.Protocol;

namespace CCM_API
{
    public class UserGroupManager: BaseManager
    {
        public UserGroupManager(IgniteManager igniteManager,
            IConfiguration configuration ): base(igniteManager,configuration) { }

        private ICache<long, UserGroup> GetDataStorage()
        {
            return igniteManager.GetIgnition().GetOrCreateCache<long, UserGroup>("UserGroups");
        }
        
        private IAtomicSequence GetIdSequence()
        {
            return igniteManager.GetIgnition().GetAtomicSequence("UserGroupsIdSeq", 100, true);
        }

        public List<UserGroup> GetAll()
        {
            var queryable =  GetDataStorage().AsCacheQueryable();
            var groupsCe = queryable.ToList(); // Where(grp => grp.Key > 0).ToList();

            if (groupsCe.Count == 0) return null;
            
            var groups = new List<UserGroup>();

            foreach (var group in groupsCe)
            {
                groups.Add(group.Value);  
            }
            return groups;
        }
        
        public UserGroup Get(long id)
        {
            var storage = GetDataStorage();
            if (storage.ContainsKey(id))
            {
                return storage[id];
            }
            else
            {
                return null;
            }
        }
        
        public UserGroup FindOne(string name)
        {

            var queryable = GetDataStorage().AsCacheQueryable();

            var grplist = queryable.Where(grp => grp.Value.Name == name);

            if (!grplist.Any()) return null;
            
            var grpItem = grplist.FirstOrDefault();

            return grpItem.Value;
        }
        
        public bool Exists(long id)
        {
            var storage = GetDataStorage();
            return storage.ContainsKey(id);
        }
        
        public UserGroup[] GetGroupsOfUser(User user)
        {
            
            var result = new List<UserGroup>();
            
            var queryable = GetDataStorage().AsCacheQueryable();

            foreach (var usrGroupElem in queryable.ToArray() )
            {
                if(usrGroupElem.Value.UsersIds.Any(uid => uid == user.AccountId)) result.Add(usrGroupElem.Value);
            }

            return result.ToArray();
        }
        
        public ObjectOperationResponse Create(UserGroup group)
        {
            var result = new ObjectOperationResponse()
            {
                Status = ObjectOperationStatus.Error,
                Message = ""
            };

            if (group == null)
            {
                result.Message = "Group cannot be null";
                return result;
            }
            
            if (group.Id != -1)
            {
                result.Message = "You can't define an id on a new group";
                return result;
            }
            var storage = GetDataStorage();
            var seqId = GetIdSequence();

            group.Id = seqId.Increment();
            
            storage.PutAsync(group.Id, group);

            result.Status = ObjectOperationStatus.Created;
            result.IdRef = group.Id;
            
            return result;
            
            
        }

        public ObjectOperationResponse Update(UserGroup group)
        {
            if (!GetDataStorage().ContainsKey(group.Id))
            {
                return new ObjectOperationResponse()
                {
                    Status = ObjectOperationStatus.NotFound,
                    Message = "The id specified does not bellong to any object",
                    IdRef = group.Id
                };
            }

            if (GetDataStorage()[group.Id].Name != group.Name && FindOne(group.Name) != null)
            {
                return new ObjectOperationResponse()
                {
                    Status = ObjectOperationStatus.Error,
                    Message = "The name used already exists",
                    IdRef = group.Id
                };
            }

            GetDataStorage()[group.Id] = group;

            return new ObjectOperationResponse()
            {
                Status = ObjectOperationStatus.Updated,
                IdRef = group.Id
            };

        }
        public ObjectOperationResponse DeleteById(long id)
        {
            var result = new ObjectOperationResponse()
            {
                IdRef = id,
                Status = ObjectOperationStatus.Error,
                Message = "Unkown error"
            };

            if (!GetDataStorage().ContainsKey(id))
            {
                result.Status = ObjectOperationStatus.NotFound;
                result.Message = "The id specified does not bellong to any object";
                return result;
            }

            if (GetDataStorage().Remove(id))
            {
                result.Status = ObjectOperationStatus.Deleted;
                result.Message = "";
            }
            else
            {
                result.Status = ObjectOperationStatus.Error;
                result.Message = "Error deleting account";
            }

            return result;
        }
        
    }
}