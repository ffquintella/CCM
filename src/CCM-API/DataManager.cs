using System;
using System.IO;
using CCM_API.Helpers;
using CCM_API.Security;
using Domain.Protocol;
using Microsoft.Extensions.Configuration;
using Newtonsoft.Json;

namespace CCM_API
{
    public class DataManager: BaseManager
    {
        public DataManager(
            IgniteManager igniteManager,
            IConfiguration configuration,
            FileManager fileManager,
            UserManager userManager,
            UserGroupManager groupManager,
            RoleManager roleManager,
            AccountManager accountManager,
            EnvironmentManager envManager) : base(igniteManager, configuration)
        {

            this.fileManager = fileManager;
            this.userManager = userManager;
            this.accountManager = accountManager;
            this.groupManager = groupManager;
            this.roleManager = roleManager;
            this.envManager = envManager;
        }

        private readonly FileManager fileManager;
        private readonly UserManager userManager;
        private readonly UserGroupManager groupManager;
        private readonly AccountManager accountManager;
        private readonly RoleManager roleManager;
        private readonly EnvironmentManager envManager;
        
        public Tuple<bool, string> CreateDataFileV1(bool encrypt, string password)
        {

            try
            {
                var fileName = fileManager.CreateNewApiFile("ccmd");
                if (fileName == null)
                {
                    logger.Error("Error creating file");
                    return new Tuple<bool, string>(false, "Error creating file");
                }

                var users = userManager.GetAll();
                var usersJson = JsonConvert.SerializeObject(users);
                string usersCompressed;

                if (encrypt)
                {
                    var usersEncoded = EncryptionManager.EncryptString(usersJson, password);
                    usersCompressed = StringHelper.ConvertToBase64(CompressionHelper.Zip(usersEncoded));
                }
                else
                {
                    usersCompressed = StringHelper.ConvertToBase64(CompressionHelper.Zip(usersJson));
                }

                var accounts = accountManager.GetAll();
                var accountsJson = JsonConvert.SerializeObject(accounts);
                string accountsCompressed;

                if (encrypt)
                {
                    var accountsEncoded = EncryptionManager.EncryptString(accountsJson, password);
                    accountsCompressed = StringHelper.ConvertToBase64(CompressionHelper.Zip(accountsEncoded));
                }
                else
                {
                    accountsCompressed = StringHelper.ConvertToBase64(CompressionHelper.Zip(accountsJson));
                }
                
                var groups = groupManager.GetAll();
                var groupsJson = JsonConvert.SerializeObject(groups);
                string groupsCompressed;

                if (encrypt)
                {
                    var groupsEncoded = EncryptionManager.EncryptString(groupsJson, password);
                    groupsCompressed = StringHelper.ConvertToBase64(CompressionHelper.Zip(groupsEncoded));
                }
                else
                {
                    groupsCompressed = StringHelper.ConvertToBase64(CompressionHelper.Zip(groupsJson));
                }
                
                var roles = roleManager.GetAll();
                var rolesJson = JsonConvert.SerializeObject(roles);
                string rolesCompressed;

                if (encrypt)
                {
                    var rolesEncoded = EncryptionManager.EncryptString(rolesJson, password);
                    rolesCompressed = StringHelper.ConvertToBase64(CompressionHelper.Zip(rolesEncoded));
                }
                else
                {
                    rolesCompressed = StringHelper.ConvertToBase64(CompressionHelper.Zip(rolesJson));
                }
                
                var envs = envManager.GetAll(true);
                var envsJson = JsonConvert.SerializeObject(envs);
                string envsCompressed;

                if (encrypt)
                {
                    var envsEncoded = EncryptionManager.EncryptString(envsJson, password);
                    envsCompressed = StringHelper.ConvertToBase64(CompressionHelper.Zip(envsEncoded));
                }
                else
                {
                    envsCompressed = StringHelper.ConvertToBase64(CompressionHelper.Zip(envsJson));
                }
                
                
                var dataFile = new DataFile()
                {
                    CreationDateTime = DateTime.Now,
                    Version = 1, 
                    FileName = fileName.Item2, 
                    IsEncrypted = encrypt,
                    UsersData = usersCompressed,
                    AccountsData = accountsCompressed,
                    UsersGroupsData = groupsCompressed,
                    RolesData = rolesCompressed,
                    EnvironmentsData = envsCompressed
                };

                // serialize JSON directly to a file
                using (StreamWriter file = File.CreateText(fileName.Item1))
                {
                    JsonSerializer serializer = new JsonSerializer();
                    serializer.Serialize(file, dataFile);
                }
                
                  
            
                return new Tuple<bool, string>(true, fileName.Item2);
            }
            catch (Exception ex)
            {
                return new Tuple<bool, string>(false, ex.Message);
            }
            
        }
    }
}