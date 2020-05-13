using System;
using System.Collections.Generic;
using Apache.Ignite.Core.Cache.Configuration;
using Apache.Ignite.Core.Cache.Expiry;
using Apache.Ignite.Core.Common;
using CCM_API.Factories;
using Domain;
using Domain.Security;
using Environment = Domain.Environment;

namespace CCM_API.Helpers
{
    public static class IgniteCacheHelper
    {
        public static CacheConfiguration[] GetAllCaches()
        {
            var configs = new[]
            {
                new CacheConfiguration
                {
                    Name = "default", // Use default region
                    DataRegionName = "defaultRegion",
                    CacheMode = CacheMode.Replicated,
                    //Backups = 1,
                    RebalanceBatchSize = 1024 * 1024,
                },
                new CacheConfiguration
                {
                    Name = "Users", 
                    DataRegionName = "defaultRegion",
                    GroupName = "Authorization",
                    CacheMode = CacheMode.Replicated,
                    QueryEntities = new QueryEntity[] { 
                        GetUserQueryEntity(),
                    },
                    RebalanceBatchSize = 1024 * 1024,
                },
                new CacheConfiguration
                {
                    Name = "Environments", 
                    DataRegionName = "defaultRegion",
                    GroupName = "Data",
                    CacheMode = CacheMode.Replicated,
                    QueryEntities = new QueryEntity[] { 
                        GetEnvironmentQueryEntity(),
                    },
                    RebalanceBatchSize = 1024 * 1024,
                },
                new CacheConfiguration
                {
                    Name = "Accounts", 
                    DataRegionName = "defaultRegion",
                    GroupName = "Authorization",
                    CacheMode = CacheMode.Replicated,
                    QueryEntities = new QueryEntity[] { 
                        GetAccountQueryEntity()
                    },
                    RebalanceBatchSize = 1024 * 1024,
                },
                new CacheConfiguration
                {
                    Name = "UserGroups", 
                    DataRegionName = "defaultRegion",
                    GroupName = "Authorization",
                    CacheMode = CacheMode.Replicated,
                    QueryEntities = new QueryEntity[] { 
                        GetUserGroupQueryEntity()
                    }
                },
                new CacheConfiguration
                {
                    Name = "Roles", 
                    DataRegionName = "defaultRegion",
                    GroupName = "Authorization",
                    CacheMode = CacheMode.Replicated,
                    QueryEntities = new QueryEntity[] { 
                        GetRoleQueryEntity()
                    }
                },
                new CacheConfiguration
                {
                    Name = "Permissions", 
                    DataRegionName = "defaultRegion",
                    GroupName = "Data",
                    CacheMode = CacheMode.Replicated,
                    QueryEntities = new QueryEntity[] { 
                        GetPermissionQueryEntity()
                    }
                },
                new CacheConfiguration
                {
                    Name = "Applications", 
                    DataRegionName = "defaultRegion",
                    GroupName = "Data",
                    CacheMode = CacheMode.Replicated,
                    QueryEntities = new QueryEntity[] { 
                        GetApplicationQueryEntity()
                    }
                },
                new CacheConfiguration
                {
                    Name = "ApplicationEnvironment", 
                    DataRegionName = "defaultRegion",
                    GroupName = "Relation",
                    CacheMode = CacheMode.Replicated,
                    QueryEntities = new QueryEntity[] { 
                        GetApplicationEnvionmentQueryEntity()
                    }
                },
                new CacheConfiguration
                {
                    Name = "AuthenticationControl", 
                    DataRegionName = "inMemoryRegion",
                    GroupName = "TokenStorage",
                    CacheMode = CacheMode.Replicated,
                    EagerTtl = true,
                    ExpiryPolicyFactory =  new ExpiryPolicyFactory(TimeSpan.FromMinutes(60))
                },
                new CacheConfiguration
                {
                    Name = "metaData",
                    DataRegionName = "ccmMetaData",
                    CacheMode = CacheMode.Replicated,
                    Backups = 1,
                    RebalanceBatchSize = 1024 * 1024,
                },
                new CacheConfiguration
                {
                    Name = "inMemoryOnlyCache",
                    DataRegionName = "inMemoryRegion",
                    CacheMode = CacheMode.Replicated,
                    RebalanceBatchSize = 1024 * 1024,
                }
            };

            return configs;
        }

        private static QueryEntity GetUserQueryEntity()
        {
            var qe = new QueryEntity(typeof(long), typeof(User));
            var fields = new List<QueryField>();
            
            fields.Add(new QueryField("Id",typeof(long)));
            fields.Add(new QueryField("Active",typeof(bool)));
            fields.Add(new QueryField("Name",typeof(string)));
            fields.Add(new QueryField("Email",typeof(string)));
            fields.Add(new QueryField("PhoneNumber",typeof(string)));
            fields.Add(new QueryField("Description",typeof(string)));
            fields.Add(new QueryField("PublicIdNumber",typeof(string)));
            fields.Add(new QueryField("AccountId",typeof(long)));

            qe.Fields = fields;
            
            var indexes = new List<QueryIndex>();
            
            indexes.Add(new QueryIndex("Id"));
            indexes.Add(new QueryIndex("PublicIdNumber"));
            indexes.Add(new QueryIndex("Name"));
            indexes.Add(new QueryIndex("Email"));
            indexes.Add(new QueryIndex("AccountId"));

            qe.Indexes = indexes;

            return qe;
        }
        private static QueryEntity GetEnvironmentQueryEntity()
        {
            var qe = new QueryEntity(typeof(long), typeof(Environment));
            var fields = new List<QueryField>();
            
            fields.Add(new QueryField("Id",typeof(long)));
            fields.Add(new QueryField("Name",typeof(string)));
            fields.Add(new QueryField("Active",typeof(bool)));
            
            qe.Fields = fields;
            
            var indexes = new List<QueryIndex>();
            
            indexes.Add(new QueryIndex("Id"));
            indexes.Add(new QueryIndex("Name"));
            indexes.Add(new QueryIndex("Active"));

            qe.Indexes = indexes;
            
            return qe;
        }
        private static QueryEntity GetAccountQueryEntity()
        {
            var qe = new QueryEntity(typeof(long), typeof(Account));
            var fields = new List<QueryField>();
            
            fields.Add(new QueryField("Id",typeof(long)));
            fields.Add(new QueryField("Login",typeof(string)));
            fields.Add(new QueryField("Active",typeof(bool)));

            qe.Fields = fields;
            
            var indexes = new List<QueryIndex>();
            
            indexes.Add(new QueryIndex("Id"));
            indexes.Add(new QueryIndex("Login"));

            qe.Indexes = indexes;
            
            return qe;
        }
        private static QueryEntity GetUserGroupQueryEntity()
        {
            var qe = new QueryEntity(typeof(long), typeof(UserGroup));
            var fields = new List<QueryField>();
            
            fields.Add(new QueryField("Id",typeof(long)));
            fields.Add(new QueryField("Name",typeof(string)));
            fields.Add(new QueryField("Users",typeof(User[])));
            
            fields.Add(new QueryField("Roles",typeof(Role[])));

            qe.Fields = fields;
            
            var indexes = new List<QueryIndex>();
            
            indexes.Add(new QueryIndex("Id"));
            indexes.Add(new QueryIndex("Name"));

            qe.Indexes = indexes;
            
            return qe;
        }
        private static QueryEntity GetRoleQueryEntity()
        {
            var qe = new QueryEntity(typeof(long), typeof(Role));
            var fields = new List<QueryField>();
            
            fields.Add(new QueryField("Id",typeof(long)));
            fields.Add(new QueryField("Name",typeof(string)));
            fields.Add(new QueryField("Claims",typeof(IClaim[])));

            qe.Fields = fields;
            
            var indexes = new List<QueryIndex>();
            
            indexes.Add(new QueryIndex("Id"));
            indexes.Add(new QueryIndex("Name"));

            qe.Indexes = indexes;
            
            return qe;
        }
        private static QueryEntity GetPermissionQueryEntity()
        {
            var qe = new QueryEntity(typeof(long), typeof(Permission));
            var fields = new List<QueryField>();
            
            fields.Add(new QueryField("Id",typeof(long)));
            fields.Add(new QueryField("Type",typeof(PermissionType)));
            fields.Add(new QueryField("Consent",typeof(PermissionConsent)));
            fields.Add(new QueryField("EnvironmentId",typeof(long)));
            fields.Add(new QueryField("GroupId",typeof(long)));
            fields.Add(new QueryField("OwnerId",typeof(long)));
            
            qe.Fields = fields;
            
            var indexes = new List<QueryIndex>();
            
            indexes.Add(new QueryIndex("Id"));
            indexes.Add(new QueryIndex("EnvironmentId"));
            indexes.Add(new QueryIndex("GroupId"));
            indexes.Add(new QueryIndex("OwnerId"));

            indexes.Add( new QueryIndex("Type", "OwnerId"));

            qe.Indexes = indexes;
            
            return qe;
        }
        private static QueryEntity GetApplicationQueryEntity()
        {
            var qe = new QueryEntity(typeof(long), typeof(Application));
            var fields = new List<QueryField>();
            
            fields.Add(new QueryField("Id",typeof(long)));
            fields.Add(new QueryField("Name",typeof(string)));

            
            qe.Fields = fields;
            
            var indexes = new List<QueryIndex>();
            
            indexes.Add(new QueryIndex("Id"));
            indexes.Add(new QueryIndex("Name"));
 

            qe.Indexes = indexes;
            
            return qe;
        }
        private static QueryEntity GetApplicationEnvionmentQueryEntity()
        {
            var qe = new QueryEntity(typeof(long), typeof(Application));
            var fields = new List<QueryField>();
            
            fields.Add(new QueryField("ApplicationId",typeof(long)));
            fields.Add(new QueryField("EnvironmentId",typeof(string)));

            
            qe.Fields = fields;
            
            var indexes = new List<QueryIndex>();
            
            indexes.Add(new QueryIndex("ApplicationId", "EnvironmentId"));
            
 

            qe.Indexes = indexes;
            
            return qe;
        }
    }
}