using System;
using System.Collections.Generic;
using System.Linq;
using Apache.Ignite.Core.Cache;
using Apache.Ignite.Core.Cache.Query;
using Apache.Ignite.Core.DataStructures;
using Apache.Ignite.Linq;
using CCM_API.Security;
using Domain;
using Domain.Protocol;
using Microsoft.Extensions.Configuration;
using Serilog;
namespace CCM_API
{
    public class UserManager: BaseManager
    {
        public UserManager(
            IgniteManager igniteManager,
            IConfiguration configuration ): base(igniteManager,configuration) { } 
        
        
        private ICache<long, User> GetDataStorage()
        {
            return igniteManager.GetIgnition().GetOrCreateCache<long, User>("Users");
        }

        private IAtomicSequence GetIdSequence()
        {
            return igniteManager.GetIgnition().GetAtomicSequence("UserIdSeq", 1, true);
        }
        
        public List<User> GetAll()
        {
            var queryable =  GetDataStorage().AsCacheQueryable();
            var usersQ = queryable.ToList(); 

            //if (usersQ.Count == 0) return null;
            
            var users = new List<User>();

            foreach (var user in usersQ)
            {
                users.Add(user.Value);  
            }
            return users;
        }
        
        public List<User> GetAllActiveUsers()
        {

            var queryable = GetDataStorage().AsCacheQueryable();

            //User[] activeUsers = queryable.Where(usr => usr.Value.Active == true).Cast<User>().ToArray();
            var activeUsers = queryable.Where(usr =>  usr.Value.Active).ToArray();

            var users = new List<User>();

            foreach (var user in activeUsers)
            {
              users.Add(user.Value);  
            }
            return users;
        }

        public List<User> GetUsersInList(List<long> Ids)
        {
            var queryable = GetDataStorage().AsCacheQueryable();

            var usersq = from u in queryable
                where
                    Ids.Contains(u.Key)
                select u;

            var users = new List<User>();
            foreach (var userq in usersq)
            {
                users.Add(userq.Value);
            }

            return users;
        }
        public User FindUserByAccountId(long accountId)
        {
            var queryable = GetDataStorage().AsCacheQueryable();

            //User[] activeUsers = queryable.Where(usr => usr.Value.Active == true).Cast<User>().ToArray();
            var activeUsers = queryable.Where(usrReg =>  usrReg.Value.AccountId == accountId).ToArray();

            if (activeUsers.Length == 0) return null;

            return activeUsers.First().Value;
        }
        
        public User FindUserByUserId(long userId)
        {
            var queryable = GetDataStorage().AsCacheQueryable();

            //User[] activeUsers = queryable.Where(usr => usr.Value.Active == true).Cast<User>().ToArray();
            var activeUsers = queryable.Where(usrReg =>  usrReg.Value.Id == userId).ToArray();

            if (activeUsers.Length == 0) return null;

            return activeUsers.First().Value;
        }

        public ObjectOperationResponse UpdateUser(User user)
        {
            var result = new ObjectOperationResponse()
            {
                Status = ObjectOperationStatus.Error,
                Message = ""
            };
            
            var storage = GetDataStorage();

            if (user == null || user.Id < 0 || !storage.ContainsKey(user.Id)) return result;

            if (storage.Replace(user.Id, user))
            {
                result.Status = ObjectOperationStatus.Updated;
                return result;
            }

            return result;
        }

        public ObjectOperationResponse Create(User user)
        {
            var result = new ObjectOperationResponse()
            {
                Status = ObjectOperationStatus.Error,
                Message = ""
            };

            if (user == null)
            {
                result.Message = "User cannot be null";
                return result;
            }
            
            if (user.Id != -1)
            {
                result.Message = "You can't define an id on a new user";
                return result;
            }
            var storage = GetDataStorage();
            var seqId = GetIdSequence();
            
            //Let's verify if there is already a user with this account id
            var queryable = storage.AsCacheQueryable();

            var nusr = queryable.Count(usr => usr.Value.AccountId == user.AccountId);

            if (nusr > 0)
            {
                result.Message = "There is already a user associated with this account";
                return result;
            }
            
            // We need this because of the circular dependency in DI
            var accountCache =  igniteManager.GetIgnition().GetOrCreateCache<long, Account>("Accounts");
            
            //Let's check if the account associated with this user exists
            if (!accountCache.ContainsKey(user.AccountId))
            {
                result.Message = "This account id doesn't exist";
                return result;
            }

            user.Id = seqId.Increment();
            
            storage.PutAsync(user.Id, user);

            result.Status = ObjectOperationStatus.Created;
            result.IdRef = user.Id;
            
            return result;
            
            
        }
        
        public ObjectOperationResponse DeleteById(long id)
        {
            var result = new ObjectOperationResponse()
            {
                IdRef = id,
                Status = ObjectOperationStatus.Error
            };

            if (GetDataStorage().Remove(id))
            {
                result.Status = ObjectOperationStatus.Deleted;
            }
            else
            {
                result.Message = "Error deleting account";
            }

            return result;
        }
        
    }
    
    
}