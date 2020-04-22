using System;
using System.Collections.Generic;
using System.Linq;
using Apache.Ignite.Core.Cache;
using Apache.Ignite.Core.DataStructures;
using Apache.Ignite.Linq;
using Domain.Protocol;
using Microsoft.Extensions.Configuration;
using Environment = Domain.Environment;

namespace CCM_API
{
    public class EnvironmentManager: BaseManager
    {
        public EnvironmentManager(
            IgniteManager igniteManager,
            IConfiguration configuration ) : base(igniteManager,configuration) { }
        
        private ICache<long, Environment> GetDataStorage()
        {
            return igniteManager.GetIgnition().GetOrCreateCache<long, Environment>("Environments");
        }
        
        private IAtomicSequence GetIdSequence()
        {
            return igniteManager.GetIgnition().GetAtomicSequence("EnvironmentIdSeq", 1, true);
        }
        
        public List<Environment> GetAll(bool getDisabled = false)
        {
            var queryable =  GetDataStorage().AsCacheQueryable();

            List<ICacheEntry<long, Environment>> envsCe;
            
            if(getDisabled) envsCe = queryable.ToList();  //.Where(grp => grp.Key > 0).ToList();
            else envsCe = queryable.Where(grp => grp.Value.Active == true).ToList();

            if (envsCe.Count == 0) return null;
            
            var envs = new List<Environment>();

            foreach (var env in envsCe)
            {
                envs.Add(env.Value);  
            }
            return envs;
        }
        
        public Environment FindById(long id)
        {
            var storage = GetDataStorage();

            try
            {
                return storage.Get(id);
            }
            catch (KeyNotFoundException ex)
            {
                return null;
            }
          
        }

        public ObjectOperationResponse Create(Environment env)
        {
            var result = new ObjectOperationResponse()
            {
                Status = ObjectOperationStatus.Error,
                Message = ""
            };

            if (env == null)
            {
                result.Message  = "Environment cannot be null";
                return result;
            }

            try
            {
                var storage = GetDataStorage();
                env.Id = GetIdSequence().Increment();
                storage.Put(env.Id, env);
                result.Status = ObjectOperationStatus.Created;
                result.IdRef = env.Id;
                return result;
            }
            catch (Exception ex)
            {
                result.Message = ex.Message;
                return result;
            }
                    
        }
        public ObjectOperationResponse Update(long id, Environment env)
        {
            var result = new ObjectOperationResponse()
            {
                Status = ObjectOperationStatus.Error,
                Message = ""
            };

            if (env == null)
            {
                result.Message  = "Environment cannot be null";
                return result;
            }

            if (env.Id != id)
            {
                result.Status = ObjectOperationStatus.Forbidden;
                result.Message  = "Cannot update id";
                return result; 
            }
            
            var storage = GetDataStorage();
            try
            {
                var envOrig = storage.Get(id);
            }
            catch (KeyNotFoundException ex)
            {
                result.Status = ObjectOperationStatus.NotFound;
                result.Message = "Key provided not found";
                return result;
            }
            
            try
            {
                storage.Put(id, env);
                result.Status = ObjectOperationStatus.Updated;
                result.IdRef = env.Id;
                return result;
            }
            catch (Exception ex)
            {
                result.Message = ex.Message;
                return result;
            }
                    
        }
        public ObjectOperationResponse Delete(long id)
        {
            var result = new ObjectOperationResponse()
            {
                Status = ObjectOperationStatus.Error,
                Message = ""
            };

            var storage = GetDataStorage();
            try
            {
                var envOrig = storage.Get(id);
            }
            catch (KeyNotFoundException ex)
            {
                result.Status = ObjectOperationStatus.NotFound;
                result.Message = "Key provided not found";
                return result;
            }
            
            try
            {
                var ok = storage.Remove(id);
                if (ok)
                {
                    result.Status = ObjectOperationStatus.Deleted;
                    result.IdRef = id;
                }
                else
                {
                    result.Status = ObjectOperationStatus.Error;
                    result.Message = "Unkwon error";
                    result.IdRef = id;
                }
                return result;
            }
            catch (Exception ex)
            {
                result.Message = ex.Message;
                return result;
            }
                    
        }
    }
}