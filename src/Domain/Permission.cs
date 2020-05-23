using System;

namespace Domain
{
    /// <summary>
    /// This class represents an access pessmission to an boject in the system
    /// </summary>
    public class Permission
    {
        /// <summary>
        /// This id identifies the permision
        /// </summary>
        public long Id { get; set; }
        /// <summary>
        /// Permisison Type determining the object to witch it refers
        /// </summary>
        public int Type { get; set; }
        /// <summary>
        /// Consent determines the kind of perfmission it is
        /// </summary>
        public int Consent { get; set; }
        /// <summary>
        /// Environment determines to witch environment it referes
        /// </summary>
        public long EnvironmentId { get; set; }
        /// <summary>
        /// Group shows witch group this permision has reference to
        /// </summary>
        public long GroupId { get; set; }
        /// <summary>
        /// The Id of the object to witch type points
        /// </summary>
        public long OwnerId { get; set; }

        /// <summary>
        /// This permision determines that the user has allaccess to the type specified
        /// </summary>
        public bool AllAccess { get; set; } = false;
    }
}