using System.Collections.Generic;
using System.Linq;
using Apache.Ignite.Core.Cache;
using Apache.Ignite.Core.DataStructures;
using Apache.Ignite.Linq;
using Domain;
using Domain.Protocol;
using Microsoft.Extensions.Configuration;
using Serilog;

namespace CCM_API
{
    public class AccountManager: BaseManager
    {
        public AccountManager(
            IgniteManager igniteManager,
            UserManager userManager,
            IConfiguration configuration ): base(igniteManager, configuration)
        {
            this._userManager = userManager;
        }

        private readonly UserManager _userManager;
        
        private ICache<long, Account> GetDataStorage()
        {
            return igniteManager.GetIgnition().GetOrCreateCache<long, Account>("Accounts");
        }
        
        private IAtomicSequence GetIdSequence()
        {
            return igniteManager.GetIgnition().GetAtomicSequence("AccountIdSeq", 1, true);
        }
        
        public List<Account> GetAll()
        {
            var queryable =  GetDataStorage().AsCacheQueryable();
            var accountsQ = queryable.ToList(); 

            var accounts = new List<Account>();

            foreach (var account in accountsQ)
            {
                accounts.Add(account.Value);  
            }
            return accounts;
        }
        
        public List<Account> GetAllActiveAccounts()
        {

            var queryable = GetDataStorage().AsCacheQueryable();
            
            var activeAccounts = queryable.Where(actReg =>  actReg.Value.Active).ToArray();

            var accounts = new List<Account>();

            foreach (var account in activeAccounts)
            {
                accounts.Add(account.Value);  
            }
            return accounts;
        }

        public Account FindByLogin(string login)
        {
            var queryable = GetDataStorage().AsCacheQueryable();
            var searchedAccounts = queryable.Where(actReg =>  actReg.Value.Active && actReg.Value.Login == login).ToArray();

            if (searchedAccounts.Length == 0)
            {
                return null;
            }

            return searchedAccounts.First().Value;
            
        }

        public bool Exists(long id)
        {
            return GetDataStorage().ContainsKey(id);
        }
        
        public Account FindById(long id)
        {

            if (!GetDataStorage().ContainsKey(id)) return null;
            return GetDataStorage()[id];
            
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
        
        public Account FindByUserId(long userId)
        {

            var user = _userManager.FindUserByUserId(userId);

            if (user == null) return null;

            var queryable = GetDataStorage().AsCacheQueryable();
            var searchedAccounts = queryable.Where(actReg =>  actReg.Value.Active && actReg.Value.Id == user.AccountId).ToArray();

            if (searchedAccounts.Length == 0)
            {
                return null;
            }

            return searchedAccounts.First().Value;
            
            
        }

        public ObjectOperationResponse Create(Account account)
        {
            var result = new ObjectOperationResponse()
            {
                Status = ObjectOperationStatus.Error,
                Message = ""
            };
            if (account == null)
            {
                result.Message = "Account cannot be null";
                return result;
            }
            
            if (account.Id != -1)
            {
                result.Message = "You can't define an id on a new account";
                return result;
            }
            
            var storage = GetDataStorage();
            var seqId = GetIdSequence();

            //Let's verify if there is already an account with this login
            var queryable = storage.AsCacheQueryable();

            var nusr = queryable.Count(acct => acct.Value.Login == account.Login);

            if (nusr > 0)
            {
                result.Message = "There is already an account using this login";
                return result;
            }
            
            account.Id = seqId.Increment();
            
            storage.PutAsync(account.Id, account);

            result.Status = ObjectOperationStatus.Created;
            result.IdRef = account.Id;
            
            return result;
            
        }

        public ObjectOperationResponse Update(Account account)
        {
            var result = new ObjectOperationResponse()
            {
                Status = ObjectOperationStatus.Error,
                Message = ""
            };
            if (account == null)
            {
                result.Message = "Account cannot be null";
                return result;
            }
            
            if (account.Id < 0)
            {
                result.Message = "You can't have a negative id on a existing account";
                return result;
            }
            
            var storage = GetDataStorage();

            if (!storage.ContainsKey(account.Id))
            {
                result.Status = ObjectOperationStatus.NotFound;
                result.IdRef = account.Id;
            
                return result;
            }
            
            storage.PutAsync(account.Id, account);

            result.Status = ObjectOperationStatus.Updated;
            result.IdRef = account.Id;
            
            return result;
            
        }
      
        
    }
}